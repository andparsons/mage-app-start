<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Configure;

/**
 * Test for pricing data provider.
 */
class PricingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stepDataProcessor;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\CategoryTree|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryTree;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\Pricing
     */
    private $dataProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollection;

    /**
     * Dummy category ID.
     *
     * @var int
     */
    private $categoryId = 12;

    /**
     * Set up for test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->stepDataProcessor = $this->getMockBuilder(
            \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryTree = $this->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\CategoryTree::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Configure\Pricing::class,
            [
                'stepDataProcessor' => $this->stepDataProcessor,
                'request' => $this->request,
                'categoryTree' => $this->categoryTree,
                'wizardStorageFactory' => $wizardStorageFactory,
                'storage' => $this->storage,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * Test for getData() method when request websites filter is empty.
     *
     * @param array $customPrice
     * @param int $price
     * @param string $priceType
     * @param array $itemData
     * @return void
     * @dataProvider getDataDataProvider
     */
    public function testGetDataWithEmptyRequestWebsitesFilter(array $customPrice, $price, $priceType, array $itemData)
    {
        $websiteId = 3;
        $requestParams = [
            'filters' => ['category_id' => $this->categoryId]
        ];

        $this->prepareGetDataMocks($customPrice, $price, $priceType, $itemData);

        $this->request->expects($this->once())->method('getParams')
            ->willReturn($requestParams);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(
                ['filters'],
                [\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM]
            )
            ->willReturnOnConsecutiveCalls(
                $requestParams['filters'],
                $websiteId
            );
        $this->storage->expects($this->once())->method('getAssignedProductSkus')->willReturn(['sku_1']);
        $this->productCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('sku', ['in' => ['sku_1']])
            ->willReturnSelf();
        $this->stepDataProcessor->expects($this->once())->method('retrieveSharedCatalogWebsiteIds')
            ->willReturn([$websiteId]);
        $this->productCollection->expects($this->once())->method('addWebsiteFilter')
            ->with([$websiteId])->willReturnSelf();

        $this->stepDataProcessor->expects($this->once())->method('getWebsites')->willReturn([$websiteId]);

        $this->assertSame($itemData, $this->dataProvider->getData());
    }

    /**
     * Test for getData() method.
     *
     * @param array $customPrice
     * @param int $price
     * @param string $priceType
     * @param array $itemData
     * @return void
     * @dataProvider getDataDataProvider
     */
    public function testGetData(array $customPrice, $price, $priceType, array $itemData)
    {
        $websiteId = 3;
        $requestParams = [
            'filters' => [
                'websites' => 3,
                'category_id' => $this->categoryId
            ]
        ];
        $this->prepareGetDataMocks($customPrice, $price, $priceType, $itemData);

        $this->request->expects($this->once())->method('getParams')
            ->willReturn($requestParams);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with('filters')
            ->willReturn($requestParams['filters']);
        $this->storage->expects($this->once())->method('getAssignedProductSkus')->willReturn(['sku_1']);
        $this->productCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('sku', ['in' => ['sku_1']])
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addWebsiteFilter')
            ->with($websiteId)->willReturnSelf();

        $this->stepDataProcessor->expects($this->once())->method('getWebsites')->willReturn([$websiteId]);

        $this->assertSame($itemData, $this->dataProvider->getData());
    }

    /**
     * Prepare mocks for testGetData() method.
     *
     * @param array $customPrice
     * @param int $price
     * @param string $priceType
     * @param array $itemData
     * @return void
     */
    private function prepareGetDataMocks(array $customPrice, $price, $priceType, array $itemData)
    {
        $sku = 'sku_1';
        $this->productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku', 'setCustomPrice', 'setPriceType', 'getPrice', 'setData', 'toArray'])
            ->getMock();
        $this->categoryTree->expects($this->once())
            ->method('getCategoryProductsCollectionById')
            ->with($this->categoryId)
            ->willReturn($this->productCollection);
        $this->productCollection->expects($this->once())->method('getSize')->willReturn(1);
        $this->productCollection->expects($this->once())->method('getItems')->willReturn([$item]);
        $this->stepDataProcessor->expects($this->once())->method('switchCurrentStore');
        $this->stepDataProcessor->expects($this->once())
            ->method('prepareCustomPrice')
            ->with($customPrice)
            ->willReturn($customPrice);
        $item->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $item->expects($this->atLeastOnce())->method('getPrice')->willReturn($price);
        $this->storage->expects($this->once())
            ->method('getProductPrices')
            ->with($sku)
            ->willReturn($customPrice);
        $item->expects($this->once())->method('setCustomPrice')->with($price)->willReturnSelf();
        $item->expects($this->once())->method('setPriceType')->with($priceType)->willReturnSelf();
        $this->storage->expects($this->atLeastOnce())
            ->method('getTierPrices')
            ->withConsecutive([$sku, false], [null, true])
            ->willReturnOnConsecutiveCalls(['tier_price'], [[['is_changed' => true]]]);
        $this->stepDataProcessor->expects($this->once())
            ->method('isCustomPriceEnabled')
            ->with($customPrice)
            ->willReturn(true);
        $item->expects($this->exactly(3))
            ->method('setData')
            ->withConsecutive(['origin_price', $price], ['tier_price_count', 1], ['custom_price_enabled', true])
            ->willReturnSelf();
        $item->expects($this->once())->method('toArray')->willReturn($itemData);
        $this->stepDataProcessor->expects($this->once())
            ->method('modifyData')
            ->with(
                [
                    'totalRecords' => 1,
                    'items' => [0 => $itemData]
                ]
            )
            ->willReturn($itemData);
    }

    /**
     * Data provider for getData method.
     *
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            [
                ['value_type' => 'fixed', 'price' => 10],
                10,
                'fixed',
                [
                    'custom_price' => 10,
                    'price_type' => 'fixed',
                    'origin_price' => 10,
                    'tier_price_count' => 1,
                    'custom_price_enabled' => true,
                    'websites' => [3],
                    'isChanged' => true
                ]
            ],
            [
                ['value_type' => 'percent', 'percentage_value' => 5],
                5,
                'percent',
                [
                    'custom_price' => 5,
                    'price_type' => 'percent',
                    'origin_price' => 5,
                    'tier_price_count' => 1,
                    'custom_price_enabled' => true,
                    'websites' => [3],
                    'isChanged' => true
                ]
            ],
        ];
    }
}
