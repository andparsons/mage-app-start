<?php
namespace Magento\AdminGws\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogRootCategoryCanBeAdded implements ObserverInterface
{
    /**
     * @var \Magento\AdminGws\Model\Blocks
     */
    protected $blocks;

    /**
     * @param \Magento\AdminGws\Model\Blocks $blocks
     */
    public function __construct(
        \Magento\AdminGws\Model\Blocks $blocks
    ) {
        $this->blocks = $blocks;
    }

    /**
     * Update role store group ids in helper and role
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->blocks->catalogRootCategoryCanBeAdded($observer);
    }
}
