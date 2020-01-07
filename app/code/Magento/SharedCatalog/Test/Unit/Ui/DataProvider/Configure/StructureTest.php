<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Configure;

use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Unit test for Structure data provider.
 */
class StructureTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\CategoryTree|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryTree;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\Structure
     */
    private $structureDataProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var string
     */
    private $configureKey = 'configure_key_value';

    /**
     * @var int
     */
    private $categoryId = 1;

    /**
     * Set up.
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
        $this->wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->categoryTree = $this->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\CategoryTree::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')->with(['key' => $this->configureKey])->willReturn($this->storage);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->structureDataProvider = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Configure\Structure::class,
            [
                'request' => $this->request,
                'stepDataProcessor' => $this->stepDataProcessor,
                'categoryTree' => $this->categoryTree,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * Test for getData() method when request websites filter is empty.
     *
     * @return void
     */
    public function testGetDataEmptyWebsitesFilter()
    {
        $expectedResult = ['totalRecords' => 1, 'items' => ['product_data_modified']];
        $requestParams = [
            'filters' => ['category_id' => $this->categoryId],
            UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY => $this->configureKey,
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => 1
        ];
        $websiteId = 1;

        $this->prepareGetDataMocks($requestParams, $expectedResult);
        $this->stepDataProcessor->expects($this->once())->method('retrieveSharedCatalogWebsiteIds')
            ->willReturn([$websiteId]);
        $this->collection->expects($this->once())->method('addWebsiteFilter')
            ->with([$websiteId])->willReturnSelf();

        $this->assertEquals($expectedResult, $this->structureDataProvider->getData());
    }

    /**
     * Test for getData() method.
     *
     * @return void
     */
    public function testGetData()
    {
        $expectedResult = ['totalRecords' => 1, 'items' => ['product_data_modified']];
        $requestParams = [
            'filters' => [
                'websites' => 1,
                'category_id' => $this->categoryId
            ],
            UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY => $this->configureKey
        ];

        $this->prepareGetDataMocks($requestParams, $expectedResult);
        $this->collection->expects($this->once())->method('addWebsiteFilter')
            ->with($requestParams['filters']['websites'])->willReturnSelf();

        $this->assertEquals($expectedResult, $this->structureDataProvider->getData());
    }

    /**
     * Prepare mocks for testGetData() method.
     *
     * @param array $requestParams
     * @param array $expectedResult
     * @return void
     */
    private function prepareGetDataMocks(array $requestParams, array $expectedResult)
    {
        $productSku = 'sku_1';
        $productData = ['product_data'];
        $this->collection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryTree->expects($this->once())
            ->method('getCategoryProductsCollectionById')->with($this->categoryId)->willReturn($this->collection);
        $this->collection->expects($this->once())->method('getSize')->willReturn(1);
        $product = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getSku', 'setIsAssign', 'toArray'])
            ->disableOriginalConstructor()->getMock();
        $this->collection->expects($this->once())->method('getItems')->willReturn([$product]);
        $product->expects($this->once())->method('getSku')->willReturn($productSku);
        $this->storage->expects($this->once())->method('isProductAssigned')->with($productSku)->willReturn(true);
        $product->expects($this->once())->method('setIsAssign')->with(true)->willReturnSelf();
        $product->expects($this->once())->method('toArray')->willReturn($productData);
        $this->stepDataProcessor->expects($this->once())->method('modifyData')
            ->with(['totalRecords' => $expectedResult['totalRecords'], 'items' => [$productData]])
            ->willReturn($expectedResult);
        $this->request->expects($this->once())->method('getParams')
            ->willReturn($requestParams);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(
                ['filters'],
                [UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY]
            )
            ->willReturnOnConsecutiveCalls(
                $requestParams['filters'],
                $this->configureKey
            );
    }
}
