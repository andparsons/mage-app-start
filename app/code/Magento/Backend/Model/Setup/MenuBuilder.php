<?php
namespace Magento\Backend\Model\Setup;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Magento\Framework\App\DocRootLocator;

/**
 * Plugin class to remove web setup wizard from menu if application root is pub/ and no setup url variable is specified.
 * @api
 * @since 100.1.0
 */
class MenuBuilder
{
    /**
     * @var DocRootLocator
     * @since 100.1.0
     */
    protected $docRootLocator;

    /**
     * MenuBuilder constructor.
     *
     * @param DocRootLocator $docRootLocator
     */
    public function __construct(DocRootLocator $docRootLocator)
    {
        $this->docRootLocator = $docRootLocator;
    }

    /**
     * Removes 'Web Setup Wizard' from the menu if doc root is pub and no setup url variable is specified.
     *
     * @param Builder $subject
     * @param Menu $menu
     * @return Menu
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 100.1.0
     */
    public function afterGetResult(Builder $subject, Menu $menu)
    {
        if ($this->docRootLocator->isPub()) {
            $menu->remove('Magento_Backend::setup_wizard');
        }
        return $menu;
    }
}
