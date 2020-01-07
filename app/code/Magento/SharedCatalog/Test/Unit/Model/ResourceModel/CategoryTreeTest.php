<?php

namespace Magento\SharedCatalog\Test\Unit\Model\ResourceModel;

/**
 * Test for Magento/SharedCatalog/Model/ResourceModel/CategoryTree class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepository;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\CategoryTree
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepository = $this->getMockBuilder(\Magento\Catalog\Api\CategoryRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataPool = $this->getMockBuilder(
            \Magento\Framework\EntityManager\MetadataPool::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\SharedCatalog\Model\ResourceModel\CategoryTree::class,
            [
                'productCollectionFactory' => $this->productCollectionFactory,
                'categoryCollectionFactory' => $this->categoryCollectionFactory,
                'categoryRepository' => $this->categoryRepository,
                'logger' => $this->logger,
                'metadataPool' => $this->metadataPool,
            ]
        );
    }

    /**
     * Test getCategoryProductsCollectionById method.
     *
     * @param int $level
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $counter
     * @param int $collectionCounter
     * @param int|array $categoryIds
     * @return void
     * @dataProvider getCategoryProductsCollectionByIdDataProvider
     */
    public function testGetCategoryProductsCollectionById(
        $level,
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $counter,
        $collectionCounter,
        $categoryIds
    ) {
        $categoryId = 1;
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $category = $this->getMockBuilder(\Magento\Catalog\Api\Data\CategoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($productCollection);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entity);
        $entity->expects($this->atLeastOnce())->method('getLinkField')->willReturn('entity_id');
        $entity->expects($this->atLeastOnce())->method('getIdentifierField')->willReturn('entity_id');
        $productCollection
            ->expects($this->once())
            ->method('joinField')
            ->with('position', 'catalog_category_product', 'position', 'product_id=entity_id', null, 'left')
            ->willReturnSelf();
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $category->expects($this->once())->method('getLevel')->willReturn($level);
        $category->expects($counter)->method('getId')->willReturn(1);
        $this->categoryCollectionFactory
            ->expects($this->exactly($collectionCounter))
            ->method('create')
            ->willReturn($categoryCollection);
        $category->expects($this->exactly($collectionCounter))->method('getPath')->willReturn('1/2');
        $categoryCollection
            ->expects($this->exactly($collectionCounter))
            ->method('addPathsFilter')->with(['1/2'])
            ->willReturnSelf();
        $categoryCollection->expects($this->exactly($collectionCounter))->method('getAllIds')->willReturn($categoryIds);
        $productCollection->expects($this->exactly(3))->method('getSelect')->willReturn($select);
        $select
            ->expects($this->once())
            ->method('where')
            ->with('at_position.category_id IN (?)', $categoryIds)
            ->willReturnSelf();
        $select
            ->expects($this->once())
            ->method('reset')
            ->with(\Magento\Framework\DB\Select::COLUMNS)
            ->willReturnSelf();
        $select
            ->expects($this->once())
            ->method('columns')
            ->with(new \Zend_Db_Expr('DISTINCT e.entity_id, e.sku, e.type_id'))
            ->willReturnSelf();
        $this->assertSame($productCollection, $this->model->getCategoryProductsCollectionById($categoryId));
    }

    /**
     * Test getCategoryProductsCollectionById method throws exception.
     *
     * @return void
     */
    public function testGetCategoryProductsCollectionByIdWithException()
    {
        $categoryId = 1;
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->productCollectionFactory->expects($this->once())->method('create')->willReturn($productCollection);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entity);
        $entity->expects($this->atLeastOnce())->method('getLinkField')->willReturn('row_id');
        $entity->expects($this->atLeastOnce())->method('getIdentifierField')->willReturn('entity_id');
        $productCollection
            ->expects($this->once())
            ->method('joinField')
            ->with('position', 'catalog_category_product', 'position', 'product_id=entity_id', null, 'left')
            ->willReturnSelf();
        $this->categoryRepository
            ->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception)->willReturnSelf();
        $productCollection->expects($this->exactly(3))->method('getSelect')->willReturn($select);
        $select
            ->expects($this->once())
            ->method('where')
            ->with('at_position.category_id IN (?)', [])
            ->willReturnSelf();
        $select
            ->expects($this->once())
            ->method('reset')
            ->with(\Magento\Framework\DB\Select::COLUMNS)
            ->willReturnSelf();
        $select
            ->expects($this->once())
            ->method('columns')
            ->with(new \Zend_Db_Expr('DISTINCT e.entity_id, e.row_id, e.sku, e.type_id'))
            ->willReturnSelf();

        $this->assertEquals($productCollection, $this->model->getCategoryProductsCollectionById($categoryId));
    }

    /**
     * Test getCategoryCollection method.
     *
     * @return void
     */
    public function testGetCategoryCollection()
    {
        $categoryId = 1;
        $productSkus = ['SKU1', 'SKU2'];
        $productIdsQuery = 'Load product IDs query';
        $category = $this->getMockBuilder(\Magento\Catalog\Api\Data\CategoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLevel', 'getId', 'getPath'])
            ->getMockForAbstractClass();
        $categoryCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['quote'])
            ->getMockForAbstractClass();
        $categoryCollection
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);
        $connection
            ->expects($this->once())
            ->method('quote')
            ->with($productSkus)
            ->willReturn("'SKU1','SKU2'");
        $categoryCollection
            ->expects($this->exactly(4))
            ->method('getTable')
            ->withConsecutive(
                ['catalog_product_entity'],
                ['catalog_category_product'],
                ['catalog_product_entity'],
                ['catalog_category_entity']
            )->willReturnOnConsecutiveCalls(
                'catalog_product_entity',
                'catalog_category_product',
                'catalog_product_entity',
                'catalog_category_entity'
            );
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $this->categoryCollectionFactory->expects($this->once())->method('create')->willReturn($categoryCollection);
        $category->expects($this->once())->method('getPath')->willReturn('1/2');
        $categoryCollection->expects($this->once())->method('addPathsFilter')->with(['1/2'])->willReturnSelf();
        $categoryCollection
            ->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->withConsecutive(['name'], ['is_active'])
            ->willReturnSelf();
        $categoryCollection->expects($this->once())->method('setLoadProductCount')->with(false)->willReturnSelf();
        $select = $this->mockDbSelect();
        $categoryCollection->expects($this->exactly(2))->method('getSelect')->willReturn($select);
        $select->expects($this->exactly(2))->method('reset')->willReturnSelf();
        $select
            ->expects($this->exactly(2))
            ->method('from')
            ->withConsecutive(
                [['product' => 'catalog_product_entity'], 'product.entity_id'],
                [['p' => 'catalog_category_product'], 'COUNT(DISTINCT p.product_id)']
            )
            ->willReturnSelf();
        $select
            ->expects($this->exactly(3))
            ->method('where')
            ->withConsecutive(
                [
                    'product.sku IN (?)'
                ],
                ['ce.path = e.path OR ce.path LIKE CONCAT(e.path, "/%")'],
                ['e.level IN(?)', [0, 1]]
            )
            ->willReturnSelf();
        $select->expects($this->atLeastOnce())->method('__toString')->willReturn($productIdsQuery);
        $categoryCollection
            ->expects($this->once())
            ->method('joinTable')
            ->with(
                ['child_products' => 'catalog_category_product'],
                'category_id=entity_id',
                [
                    'selected_count' => new \Zend_Db_Expr(
                        'COUNT(IF(child_products.product_id IN (' . $productIdsQuery . '),1,NULL))'
                    ),
                    'product_count' => new \Zend_Db_Expr('COUNT(IF(child_products.product_id IS NULL,NULL,1))'),
                    'root_selected_count' => new \Zend_Db_Expr(
                        '(' . $select . ' AND product.sku IN ('. $this->prepareSkusForQuery($productSkus) . '))'
                    ),
                    'root_product_count' =>  new \Zend_Db_Expr('(' . $select . ')')
                ],
                null,
                'left'
            )
            ->willReturnSelf();
        $categoryCollection->expects($this->once())->method('groupByAttribute')->with('entity_id')->willReturnSelf();
        $this->assertSame($categoryCollection, $this->model->getCategoryCollection($categoryId, $productSkus));
    }

    /**
     * Test getCategoryCollection method when product SKUs array is empty.
     *
     * @return void
     */
    public function testGetCategoryCollectionWithEmptyProductSkus()
    {
        $categoryId = 1;
        $productSkus = [];
        $category = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\CategoryInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getPath']
        );
        $categoryCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Category\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollection
            ->expects($this->exactly(3))
            ->method('getTable')
            ->withConsecutive(['catalog_category_product'], ['catalog_product_entity'], ['catalog_category_entity'])
            ->willReturnOnConsecutiveCalls(
                'catalog_category_product',
                'catalog_product_entity',
                'catalog_category_entity'
            );
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $this->categoryCollectionFactory->expects($this->once())->method('create')->willReturn($categoryCollection);
        $category->expects($this->once())->method('getPath')->willReturn('1/2');
        $categoryCollection->expects($this->once())->method('addPathsFilter')->with(['1/2'])->willReturnSelf();
        $categoryCollection
            ->expects($this->exactly(2))
            ->method('addAttributeToSelect')
            ->withConsecutive(['name'], ['is_active'])
            ->willReturnSelf();
        $categoryCollection->expects($this->once())->method('setLoadProductCount')->with(false)->willReturnSelf();
        $select = $this->mockDbSelect();
        $categoryCollection->expects($this->once())->method('getSelect')->willReturn($select);
        $select->expects($this->once())->method('reset')->willReturnSelf();
        $select
            ->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                ['ce.path = e.path OR ce.path LIKE CONCAT(e.path, "/%")'],
                ['e.level IN(?)', [0, 1]]
            )
            ->willReturnSelf();
        $select
            ->expects($this->once())
            ->method('from')
            ->with(
                ['p' => 'catalog_category_product'],
                'COUNT(DISTINCT p.product_id)'
            )
            ->willReturnSelf();
        $categoryCollection
            ->expects($this->once())
            ->method('joinTable')
            ->with(
                ['child_products' => 'catalog_category_product'],
                'category_id=entity_id',
                [
                    'selected_count' => '',
                    'product_count' => new \Zend_Db_Expr('COUNT(IF(child_products.product_id IS NULL,NULL,1))'),
                    'root_selected_count' => '',
                    'root_product_count' =>  new \Zend_Db_Expr('(' . $select . ')')
                ],
                null,
                'left'
            )
            ->willReturnSelf();
        $categoryCollection->expects($this->once())->method('groupByAttribute')->with('entity_id')->willReturnSelf();
        $this->assertSame($categoryCollection, $this->model->getCategoryCollection($categoryId, $productSkus));
    }

    /**
     * Mock Db Select object.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockDbSelect()
    {
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select
            ->expects($this->exactly(2))
            ->method('joinInner')
            ->withConsecutive(
                [['product' => 'catalog_product_entity'], 'product.entity_id=p.product_id', []],
                [['ce' => 'catalog_category_entity'], 'ce.entity_id=p.category_id', []]
            )
            ->willReturnSelf();

        return $select;
    }

    /**
     * Format SKUs list for IN condition in query.
     *
     * @param array $productSkus
     * @return string
     */
    private function prepareSkusForQuery(array $productSkus)
    {
        return implode(
            ',',
            array_map(
                function ($sku) {
                    return '\'' . $sku . '\'';
                },
                $productSkus
            )
        );
    }

    /**
     * Data provider for getCategoryRootNode method.
     *
     * @return array
     */
    public function getCategoryProductsCollectionByIdDataProvider()
    {
        return [
            [3, $this->once(), 0, 1],
            [1, $this->never(), 1, [5, 6, 7]]
        ];
    }
}
