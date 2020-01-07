<?php

namespace Magento\SharedCatalog\Model\ResourceModel;

/**
 * SharedCatalog categories tree resource model.
 */
class CategoryTree
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Root category levels array.
     *
     * @var array
     */
    private $rootCategoryLevels = [0, 1];

    /**
     * CategoryTree constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->metadataPool = $metadataPool;
        $this->logger = $logger;
    }

    /**
     * Get category products by category id.
     *
     * @param int|null $categoryId [optional]
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCategoryProductsCollectionById($categoryId = null)
    {
        $collection = $this->productCollectionFactory->create();
        $metaData = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        if ($categoryId) {
            $collection->joinField(
                'position',
                'catalog_category_product',
                'position',
                'product_id=entity_id',
                null,
                'left'
            );
            $categoriesIds = $this->getAllChildrenIds($categoryId);
            $collection->getSelect()->where('at_position.category_id IN (?)', $categoriesIds);
            $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $entityLinkField = $metaData->getLinkField() == $metaData->getIdentifierField()
                ? 'e.' . $metaData->getIdentifierField()
                : 'e.' . $metaData->getIdentifierField() . ', e.' . $metaData->getLinkField();
            $collection->getSelect()->columns(
                new \Zend_Db_Expr('DISTINCT ' . $entityLinkField . ', e.sku, e.type_id')
            );
        }

        return $collection;
    }

    /**
     * Get category collection for tree.
     *
     * @param int $rootCategoryId
     * @param array $productSkus
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection($rootCategoryId, array $productSkus)
    {
        $rootCategory = $this->categoryRepository->get($rootCategoryId);
        /* @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->addPathsFilter([$rootCategory->getPath()]);
        $collection->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'is_active'
        )->setLoadProductCount(
            false
        );

        $connection = $collection->getConnection();
        $selectedCountExpression = !empty($productSkus)
            ? new \Zend_Db_Expr(
                sprintf(
                    'COUNT(IF(child_products.product_id IN (%s),1,NULL))',
                    $this->prepareSubqueryForProductIds($collection, $productSkus)
                )
            ) : '';
        $rootSubQuery = $this->prepareSubqueryForRootCategory($collection);
        $collection->joinTable(
            ['child_products' => 'catalog_category_product'],
            'category_id=entity_id',
            [
                'product_count' => new \Zend_Db_Expr('COUNT(IF(child_products.product_id IS NULL,NULL,1))'),
                'selected_count' => $selectedCountExpression,
                'root_selected_count' => !empty($productSkus)
                    ? new \Zend_Db_Expr(
                        '(' . $rootSubQuery . ' AND product.sku IN (' . $connection->quote($productSkus) . '))'
                    ) : '',
                'root_product_count' =>  new \Zend_Db_Expr('(' . $rootSubQuery . ')')
            ],
            null,
            'left'
        );

        $collection->groupByAttribute('entity_id');

        return $collection;
    }

    /**
     * Prepare sub query to get products IDs by their SKUs.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
     * @param array $productSkus
     * @return \Magento\Framework\DB\Select
     */
    private function prepareSubqueryForProductIds(
        \Magento\Catalog\Model\ResourceModel\Category\Collection $collection,
        array $productSkus
    ) {
        $select = clone $collection->getSelect();
        $select->reset();
        $select->from(
            ['product' => $collection->getTable('catalog_product_entity')],
            'product.entity_id'
        )->where(
            'product.sku IN (?)',
            $productSkus
        );

        return $select;
    }

    /**
     * Prepare sub query to count products in root categories.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
     * @return \Magento\Framework\DB\Select
     */
    private function prepareSubqueryForRootCategory(
        \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
    ) {
        $select = clone $collection->getSelect();
        $select->reset();
        $select->from(
            ['p' => $collection->getTable('catalog_category_product')],
            'COUNT(DISTINCT p.product_id)'
        )->joinInner(
            ['product' => $collection->getTable('catalog_product_entity')],
            'product.entity_id=p.product_id',
            []
        )->joinInner(
            ['ce' => $collection->getTable('catalog_category_entity')],
            'ce.entity_id=p.category_id',
            []
        )->where(
            'ce.path = e.path OR ce.path LIKE CONCAT(e.path, "/%")'
        )->where(
            'e.level IN(?)',
            $this->rootCategoryLevels
        );

        return $select;
    }

    /**
     * Get category children ids.
     *
     * @param int $categoryId
     * @return array|int
     */
    private function getAllChildrenIds($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e);
            return [];
        }
        if (!in_array($category->getLevel(), $this->rootCategoryLevels)) {
            return $category->getId();
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->addPathsFilter([$category->getPath()]);
        return $collection->getAllIds();
    }
}
