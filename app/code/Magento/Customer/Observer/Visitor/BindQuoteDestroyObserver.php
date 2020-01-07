<?php

namespace Magento\Customer\Observer\Visitor;

use Magento\Framework\Event\Observer;

/**
 * Visitor Observer
 */
class BindQuoteDestroyObserver extends AbstractVisitorObserver
{
    /**
     * bindQuoteDestroy
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->visitor->bindQuoteDestroy($observer);
    }
}
