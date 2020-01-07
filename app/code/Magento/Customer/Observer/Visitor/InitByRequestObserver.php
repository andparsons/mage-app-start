<?php

namespace Magento\Customer\Observer\Visitor;

use Magento\Framework\Event\Observer;

/**
 * Visitor Observer
 */
class InitByRequestObserver extends AbstractVisitorObserver
{
    /**
     * initByRequest
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->visitor->initByRequest($observer);
    }
}
