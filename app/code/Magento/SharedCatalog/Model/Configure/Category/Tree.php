<?php
namespace Magento\SharedCatalog\Model\Configure\Category;

use Magento\Catalog\Model\Category;

/**
 * Class that forms categories tree in shared catalog wizard.
 */
class Tree
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    private $treeResource;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\CategoryTree
     */
    private $categoryTree;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $treeResource
     * @param \Magento\SharedCatalog\Model\ResourceModel\CategoryTree $categoryTree
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\Tree $treeResource,
        \Magento\SharedCatalog\Model\ResourceModel\CategoryTree $categoryTree
    ) {
        $this->treeResource = $treeResource;
        $this->categoryTree = $categoryTree;
    }

    /**
     * Get assigned product and category ids from session, populate categories tree with data and build the tree.
     *
     * @param \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage
     * @return \Magento\Framework\Data\Tree\Node
     */
    public function getCategoryRootNode(
        \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage
    ) {
        $tree = $this->treeResource->load();
        $rootCategoryId = Category::TREE_ROOT_ID;
        $assignedProductSkus = $storage->getAssignedProductSkus();
        $assignedCategoriesIds = $storage->getAssignedCategoriesIds();
        $collection = $this->categoryTree->getCategoryCollection($rootCategoryId, $assignedProductSkus);
        $tree->addCollectionData($collection);
        $root = $tree->getNodeById($rootCategoryId);
        $this->addSharedCatalogData($root, $assignedCategoriesIds);

        return $root;
    }

    /**
     * Add assigned to shared catalog categories data to category tree.
     *
     * @param \Magento\Framework\Data\Tree\Node $node
     * @param array $assignedCategoriesIds
     * @return void
     */
    private function addSharedCatalogData(\Magento\Framework\Data\Tree\Node $node, array $assignedCategoriesIds)
    {
        $level = $node->getData('level');
        $categoryId = $node->getData('entity_id');
        $node->setData('is_checked', (bool)in_array($categoryId, $assignedCategoriesIds));

        if ($level == 0) {
            $node->setData('is_active', 1);
        }
        if ($node->getIsActive() != 1) {
            $node->setData('is_checked', false);
        }

        $children = $node->getChildren();
        foreach ($children as $child) {
            $this->addSharedCatalogData($child, $assignedCategoriesIds);
        }
    }

    /**
     * Get store root category id for building categories tree in shared catalog wizard.
     *
     * @param \Magento\Store\Api\Data\GroupInterface $store
     * @return int
     */
    protected function getStoreRootCategoryId(\Magento\Store\Api\Data\GroupInterface $store)
    {
        if ($store->getId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $rootCategoryId = Category::TREE_ROOT_ID;
        } else {
            $rootCategoryId = $store->getRootCategoryId();
        }
        return $rootCategoryId;
    }
}
