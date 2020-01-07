<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Step\Structure\Category;

/**
 * Display shared catalog categories tree at selecting products step.
 *
 * @api
 * @since 100.0.0
 */
class Tree extends \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Category\Tree
{
    /**#@+
     * Category tree routes
     */
    const TREE_INIT_ROUTE = 'shared_catalog/sharedCatalog/configure_tree_structure_get';
    const CATEGORY_ASSIGN_ROUTE = 'shared_catalog/sharedCatalog/configure_category_assign';
    /**#@-*/

    /**
     * Get URL for assigning categories.
     *
     * @return string
     */
    public function getAssignUrl()
    {
        return $this->urlBuilder->getUrl(self::CATEGORY_ASSIGN_ROUTE);
    }
}
