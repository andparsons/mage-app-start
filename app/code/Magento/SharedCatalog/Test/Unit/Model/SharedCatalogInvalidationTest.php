<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for SharedCatalogInvalidation model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SharedCatalogInvalidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogInvalidation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogInvalidation;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistry;

    /**
     * @var \Magento\CatalogPermissions\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionsConfig;

    /**
     * @var \Magento\SharedCatalog\Model\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryProductIndexer;

    /**
     * @var \Magento\CatalogPermissions\Model\Indexer\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPermissionsCategoryIndexer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->setMethods(['save', 'get', 'getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getSku', 'getCategoryIds'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->productCollectionFactory = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->indexerRegistry = $this->getMockBuilder(\Magento\Framework\Indexer\IndexerRegistry::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()->getMock();

        $this->permissionsConfig = $this->getMockBuilder(\Magento\CatalogPermissions\App\ConfigInterface::class)
            ->setMethods(['isEnabled'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogRepository = $this->getMockBuilder(\Magento\SharedCatalog\Model\Repository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->categoryProductIndexer = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Category\Product::class)
            ->setMethods(['invalidate'])
            ->disableOriginalConstructor()->getMock();

        $this->catalogPermissionsCategoryIndexer = $this
            ->getMockBuilder(\Magento\CatalogPermissions\Model\Indexer\Category::class)
            ->setMethods(['isScheduled', 'reindexList'])
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->sharedCatalogInvalidation = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\SharedCatalogInvalidation::class,
            [
                'productRepository' => $this->productRepository,
                'productCollectionFactory' => $this->productCollectionFactory,
                'eventManager' => $this->eventManager,
                'indexerRegistry' => $this->indexerRegistry,
                'permissionsConfig' => $this->permissionsConfig,
                'sharedCatalogRepository' => $this->sharedCatalogRepository
            ]
        );
    }

    /**
     * Prepare IndexerRegistry mock.
     *
     * @return void
     */
    private function prepareIndexerRegistry()
    {
        $mapForMethodGet = [
            ['catalog_category_product', $this->categoryProductIndexer],
            ['catalogpermissions_category', $this->catalogPermissionsCategoryIndexer]
        ];
        $this->indexerRegistry->expects($this->exactly(1))->method('get')->willReturnMap($mapForMethodGet);
    }

    /**
     * Test for cleanCacheByTag().
     *
     * @return void
     */
    public function testCleanCacheByTag()
    {
        $sku = 'test_sku_1';

        $this->productRepository->expects($this->exactly(1))->method('get')->willReturn($this->product);

        $this->eventManager->expects($this->exactly(1))->method('dispatch');

        $this->assertNull($this->sharedCatalogInvalidation->cleanCacheByTag($sku));
    }

    /**
     * Test for invalidateIndexRegistryItem().
     *
     * @return void
     */
    public function testInvalidateIndexRegistryItem()
    {
        $this->categoryProductIndexer->expects($this->exactly(1))->method('invalidate');

        $this->prepareIndexerRegistry();

        $this->assertNull($this->sharedCatalogInvalidation->invalidateIndexRegistryItem());
    }

    /**
     * Test for validateAssignProducts().
     *
     * @return void
     */
    public function testValidateAssignProducts()
    {
        $categoryId = 236;
        $categoryIds = [$categoryId];

        $productSku = 'ASDF23526';
        $this->product->expects($this->any())->method('getSku')->willReturn($productSku);
        $this->product->expects($this->any())->method('getCategoryIds')->willReturn($categoryIds);

        $products = [$this->product];

        $expected = [$productSku];
        $result = $this->sharedCatalogInvalidation->validateAssignProducts($products, $categoryIds);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test validateAssignProducts() with Exception.
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @return void
     */
    public function testValidateAssignProductsWithException()
    {
        $categoryId = 236;
        $productsCategoryId = 356;
        $categoryIds = [$categoryId];

        $productSku = 'ASDF23526';
        $this->product->expects($this->any())->method('getSku')->willReturn($productSku);
        $productCategoryIds = [$productsCategoryId];
        $this->product->expects($this->any())->method('getCategoryIds')->willReturn($productCategoryIds);

        $products = [$this->product];

        $this->sharedCatalogInvalidation->validateAssignProducts($products, $categoryIds);
    }

    /**
     * Test for validateUnassignProducts().
     *
     * @return void
     */
    public function testValidateUnassignProducts()
    {
        $productSku = 'ASDF23526';
        $this->product->expects($this->exactly(2))->method('getSku')->willReturn($productSku);

        $products = [$this->product];

        $this->productRepository->expects($this->exactly(1))->method('get')->willReturn($this->product);

        $expected = [$productSku];
        $result = $this->sharedCatalogInvalidation->validateUnassignProducts($products);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for checkProductExist().
     *
     * @return void
     */
    public function testCheckProductExist()
    {
        $productSku = 'ASDF23526';

        $this->productRepository->expects($this->exactly(1))->method('get')->willReturn($this->product);

        $expected = $this->product;
        $result = $this->sharedCatalogInvalidation->checkProductExist($productSku);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test checkProductExist() with Exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testCheckProductExistWithException()
    {
        $productSku = 'ASDF23526';

        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->productRepository->expects($this->exactly(1))->method('get')->willThrowException($exception);

        $expected = $this->product;
        $result = $this->sharedCatalogInvalidation->checkProductExist($productSku);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for reindexCatalogPermissions().
     *
     * @return void
     */
    public function testReindexCatalogPermissions()
    {
        $reindexCategoryIds = [23];

        $isEnabled = true;
        $this->permissionsConfig->expects($this->exactly(1))->method('isEnabled')->willReturn($isEnabled);
        $this->catalogPermissionsCategoryIndexer->expects($this->exactly(1))->method('reindexList');

        $this->prepareIndexerRegistry();

        $this->assertNull($this->sharedCatalogInvalidation->reindexCatalogPermissions($reindexCategoryIds));
    }

    /**
     * Test for checkSharedCatalogExist().
     *
     * @return void
     */
    public function testCheckSharedCatalogExist()
    {
        $sharedCatalogId = 23463;

        $this->sharedCatalogRepository->expects($this->exactly(1))->method('get')->willReturn($this->sharedCatalog);

        $result = $this->sharedCatalogInvalidation->checkSharedCatalogExist($sharedCatalogId);
        $this->assertEquals($this->sharedCatalog, $result);
    }

    /**
     * Test for checkSharedCatalogExist() with Exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testCheckSharedCatalogExistWithException()
    {
        $sharedCatalogId = 23463;

        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->sharedCatalogRepository->expects($this->exactly(1))->method('get')->willThrowException($exception);

        $this->sharedCatalogInvalidation->checkSharedCatalogExist($sharedCatalogId);
    }
}
