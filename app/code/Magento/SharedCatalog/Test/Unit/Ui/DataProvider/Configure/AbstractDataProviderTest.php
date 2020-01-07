<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Configure;

/**
 * Test for configure abstract data provider.
 */
class AbstractDataProviderTest extends \PHPUnit\Framework\TestCase
{
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
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\AbstractDataProvider
     */
    private $dataProvider;

    /**
     * Set up for test.
     *
     * @return void
     */
    protected function setUp()
    {
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
        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->withConsecutive(
                ['filters'],
                [\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY]
            )
            ->willReturnOnConsecutiveCalls(
                ['category_id' => 12],
                'configure_key'
            );
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->dataProvider = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Ui\DataProvider\Configure\AbstractDataProvider::class,
            [
                'name' => 'test_name',
                'primaryFieldName' => 'primary_field_name',
                'requestFieldName' => 'request_field_name',
                'request' => $this->request,
                'wizardStorageFactory' => $wizardStorageFactory,
                'categoryTree' => $this->categoryTree,
                'storeManager' => $this->storeManager,
                'meta' => [],
                'data' => [],
            ],
            '',
            true,
            false,
            true,
            []
        );
    }

    /**
     * Test addFilter method with "name" field.
     *
     * @return void
     */
    public function testAddFilterNotFulltext()
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->exactly(2))->method('getField')->willReturn('name');
        $filter->expects($this->once())->method('getConditionType')->willReturn('eq');
        $filter->expects($this->once())->method('getValue')->willReturn('test_name');
        $this->categoryTree->expects($this->once())
            ->method('getCategoryProductsCollectionById')
            ->with(12)
            ->willReturn($productCollection);
        $productCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('name', ['eq' => 'test_name'])
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addWebsiteNamesToResult')->willReturnSelf();

        $this->dataProvider->addFilter($filter);
    }

    /**
     * Test addFilter method fulltext.
     *
     * @return void
     */
    public function testAddFilterFulltext()
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())->method('getField')->willReturn('fulltext');
        $filter->expects($this->exactly(2))->method('getValue')->willReturnOnConsecutiveCalls('test_name', 'test_sku');
        $this->categoryTree->expects($this->once())
            ->method('getCategoryProductsCollectionById')
            ->with(12)
            ->willReturn($productCollection);
        $productCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->with(
                [
                    ['attribute' => 'name', 'like' => "%test_name%"],
                    ['attribute' => 'sku', 'like' => "%test_sku%"]
                ]
            )
            ->willReturnSelf();
        $productCollection->expects($this->once())->method('addWebsiteNamesToResult')->willReturnSelf();

        $this->dataProvider->addFilter($filter);
    }

    /**
     * Test addFilter method with "store_id" field.
     *
     * @return void
     */
    public function testAddFilterStoreId()
    {
        $storeGroupId = '2';
        $storeId = 3;

        $filter = $this->createMock(\Magento\Framework\Api\Filter::class);
        $filter->expects($this->once())
            ->method('getField')
            ->willReturn('store_id');
        $filter->expects($this->once())
            ->method('getValue')
            ->willReturn($storeGroupId);

        $productCollection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $productCollection->expects($this->once())
            ->method('addWebsiteNamesToResult')
            ->willReturnSelf();
        $this->categoryTree->expects($this->once())
            ->method('getCategoryProductsCollectionById')
            ->with(12)
            ->willReturn($productCollection);

        $storeGroup = $this->createMock(\Magento\Store\Model\Group::class);
        $this->storeManager->expects($this->once())
            ->method('getGroup')
            ->with($storeGroupId)
            ->willReturn($storeGroup);
        $storeGroup->method('getDefaultStoreId')
            ->willReturn($storeId);
        $productCollection->expects($this->once())
            ->method('addStoreFilter')
            ->with($storeId)
            ->willReturnSelf();

        $this->dataProvider->addFilter($filter);
    }
}
