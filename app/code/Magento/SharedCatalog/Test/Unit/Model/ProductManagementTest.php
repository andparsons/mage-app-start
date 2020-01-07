<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Test ProductManagement model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\ProductManagement
     */
    private $productManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Model\ProductSharedCatalogsLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productSharedCatalogsLoader;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogInvalidation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogInvalidation;

    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogCategoryManagement;

    /**
     * @var SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * @var \Magento\Framework\Api\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteria;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var \Magento\SharedCatalog\Api\Data\ProductItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProduct;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->productSharedCatalogsLoader = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductSharedCatalogsLoader::class)
            ->setMethods(['getAssignedSharedCatalogs'])
            ->disableOriginalConstructor()->getMock();

        $this->productItemRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\ProductItemRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogInvalidation = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogInvalidation::class)
            ->setMethods([
                'checkSharedCatalogExist', 'validateAssignProducts', 'cleanCacheByTag',
                'invalidateIndexRegistryItem', 'validateUnassignProducts'
            ])
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogCategoryManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\CategoryManagementInterface::class)
            ->setMethods(['getCategories'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalog = $this->getMockBuilder(SharedCatalogInterface::class)
            ->setMethods(['getCustomerGroupId', 'getId', 'getType'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()->getMock();

        $this->searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogProductItemRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemRepository::class)
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogProduct = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->setMethods(['getSku', 'setSku', 'setCustomerGroupId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogProductItemManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\ProductItemManagementInterface::class)
            ->setMethods(['addItems', 'saveItem', 'deleteItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productManagement = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\ProductManagement::class,
            [
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'productSharedCatalogsLoader' => $this->productSharedCatalogsLoader,
                'productItemRepository' => $this->productItemRepository,
                'sharedCatalogInvalidation' => $this->sharedCatalogInvalidation,
                'sharedCatalogCategoryManagement' => $this->sharedCatalogCategoryManagement,
                'sharedCatalogProductItemRepository' => $this->sharedCatalogProductItemRepository,
                'sharedCatalogProductItemManagement' => $this->sharedCatalogProductItemManagement,
            ]
        );
    }

    /**
     * Prepare SharedCatalogProductItemRepository mock.
     *
     * @return void
     */
    private function prepareSharedCatalogProductItemRepository()
    {
        $sharedCatalogProductSearchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $sharedCatalogProducts = [$this->sharedCatalogProduct];
        $sharedCatalogProductSearchResults
            ->expects($this->atLeastOnce())->method('getItems')
            ->willReturn($sharedCatalogProducts);
        $sharedCatalogProductSearchResults->expects($this->any())->method('getTotalCount')->willReturn(1);
        $this->sharedCatalogProductItemRepository
            ->expects($this->atLeastOnce())->method('getList')
            ->willReturn($sharedCatalogProductSearchResults);
    }

    /**
     * Prepare SharedCatalogRepository mock.
     *
     * @return void
     */
    private function prepareSharedCatalogRepository()
    {
        $sharedCatalogSearchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $sharedCatalogs = [$this->sharedCatalog];
        $sharedCatalogSearchResults->expects($this->once())->method('getItems')->willReturn($sharedCatalogs);

        $this->sharedCatalogRepository->expects($this->once())->method('getList')
            ->willReturn($sharedCatalogSearchResults);
    }

    /**
     * Test getProducts().
     *
     * @return void
     */
    public function testGetProducts()
    {
        $sharedCatalogId = 234;

        $customerGroupId = 223;
        $this->sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);

        $this->sharedCatalogInvalidation->expects($this->once())->method('checkSharedCatalogExist')
            ->willReturn($this->sharedCatalog);

        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($this->searchCriteria);
        $this->searchCriteria->expects($this->once())->method('setCurrentPage')->with(1);

        $sku = 'HSVC347458';
        $this->sharedCatalogProduct->expects($this->once())->method('getSku')->willReturn($sku);

        $this->prepareSharedCatalogProductItemRepository();

        $result = $this->productManagement->getProducts($sharedCatalogId);

        $this->assertEquals([$sku], $result);
    }

    /**
     * Test assignProducts().
     *
     * @return void
     */
    public function testAssignProducts()
    {
        $sharedCatalogId = 234;
        $products = [$this->product];

        $this->sharedCatalogInvalidation->expects($this->once())->method('checkSharedCatalogExist')
            ->willReturn($this->sharedCatalog);

        $customerGroupId = 223;
        $this->sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->sharedCatalog->expects($this->once())->method('getId')->willReturn($sharedCatalogId);
        $sharedCatalogType = SharedCatalogInterface::TYPE_PUBLIC;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogType);

        $sharedCatalogCategoryId = 83;
        $sharedCatalogCategoryIds = [$sharedCatalogCategoryId];
        $this->sharedCatalogCategoryManagement->expects($this->once())->method('getCategories')
            ->willReturn($sharedCatalogCategoryIds);

        $sku = 'FGJFG4554345';
        $skus = [$sku];
        $this->sharedCatalogInvalidation->expects($this->once())->method('validateAssignProducts')
            ->willReturn($skus);

        $this->sharedCatalogProductItemManagement->expects($this->atLeastOnce())->method('addItems')->willReturnSelf();

        $this->assertTrue($this->productManagement->assignProducts($sharedCatalogId, $products));
    }

    /**
     * Test unassignProducts().
     *
     * @return void
     */
    public function testUnassignProducts()
    {
        $sharedCatalogId = 234;
        $products = [$this->product];
        $sku = 'FGJFG4554345';
        $skus = [$sku];

        $this->sharedCatalogInvalidation->expects($this->once())->method('checkSharedCatalogExist')
            ->willReturn($this->sharedCatalog);

        $this->sharedCatalogInvalidation->expects($this->once())->method('validateUnassignProducts')
            ->willReturn($skus);

        $customerGroupId = 223;
        $this->sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $sharedCatalogType = SharedCatalogInterface::TYPE_PUBLIC;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogType);

        //delete items
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->searchCriteria);

        $this->sharedCatalogProduct->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);

        $this->prepareSharedCatalogProductItemRepository();
        $sharedCatalogItemIsDeleted = true;
        $this->sharedCatalogProductItemRepository->expects($this->atLeastOnce())->method('deleteItems')
            ->willReturn($sharedCatalogItemIsDeleted);

        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())->method('cleanCacheByTag');
        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())->method('invalidateIndexRegistryItem');

        $this->assertTrue($this->productManagement->unassignProducts($sharedCatalogId, $products));
    }

    /**
     * Test updateProductSharedCatalogs().
     *
     * @param int $sharedCatalogType
     * @param int $expectedMethodCalls
     * @return void
     * @dataProvider updateProductSharedCatalogsDataProvider
     */
    public function testUpdateProductSharedCatalogs(int $sharedCatalogType, int $expectedMethodCalls): void
    {
        $sku = 'FGJFG4554';
        $sharedCatalogId = 435;
        $sharedCatalogIds = [$sharedCatalogId];

        $assignedSharedCatalogs = [25 => $this->sharedCatalog];
        $this->productSharedCatalogsLoader->expects($this->once())->method('getAssignedSharedCatalogs')
            ->willReturn($assignedSharedCatalogs);

        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($this->searchCriteria);

        $this->prepareSharedCatalogRepository();

        $this->sharedCatalog->expects($this->once())
            ->method('getType')
            ->willReturn($sharedCatalogType);

        $customerGroupId = 223;
        $this->sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);

        $this->sharedCatalogProductItemManagement->expects($this->exactly($expectedMethodCalls))->method('saveItem');
        $this->sharedCatalogProductItemManagement->expects($this->once())->method('deleteItems')->willReturnSelf();

        $this->assertNull($this->productManagement->updateProductSharedCatalogs($sku, $sharedCatalogIds));
    }

    /**
     * @return array
     */
    public function updateProductSharedCatalogsDataProvider(): array
    {
        return [
            [
                'sharedCatalogType' => SharedCatalogInterface::TYPE_PUBLIC,
                'expectedMethodCalls' => 2,
            ],
            [
                'sharedCatalogType' => SharedCatalogInterface::TYPE_CUSTOM,
                'expectedMethodCalls' => 1,
            ],
        ];
    }

    /**
     * Test reassignProducts().
     *
     * @return void
     */
    public function testReassignProducts()
    {
        $sku = 'FGJFG4554345';
        $skus = [$sku];

        $customerGroupId = 223;
        $this->sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $sharedCatalogType = SharedCatalogInterface::TYPE_PUBLIC;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogType);

        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->searchCriteria);

        $this->sharedCatalogProduct->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);

        $this->prepareSharedCatalogProductItemRepository();
        $sharedCatalogItemIsDeleted = true;
        $this->sharedCatalogProductItemRepository->expects($this->atLeastOnce())->method('deleteItems')
            ->willReturn($sharedCatalogItemIsDeleted);

        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())->method('cleanCacheByTag');
        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())->method('invalidateIndexRegistryItem');

        $this->sharedCatalogProductItemManagement->expects($this->atLeastOnce())->method('addItems')->willReturnSelf();

        $result = $this->productManagement->reassignProducts($this->sharedCatalog, $skus);
        $this->assertEquals($this->productManagement, $result);
    }
}
