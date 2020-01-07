<?php
namespace Magento\Framework\Event\Test\Unit\Invoker;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ObserverExample implements ObserverInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        //do nothing
    }
}
