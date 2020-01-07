<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Step\Pricing\Category;

/**
 * Display shared catalog categories tree at pricing step.
 *
 * @api
 * @since 100.0.0
 */
class Tree extends \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Category\Tree
{
    /**#@+
     * Category tree routes
     */
    const TREE_INIT_ROUTE = 'shared_catalog/sharedCatalog/configure_tree_pricing_get';
    /**#@-*/
}
