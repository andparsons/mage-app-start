<?php

namespace Magento\Customer\Observer\Visitor;

use Magento\Framework\Event\Observer;

/**
 * Visitor Observer
 */
class SaveByRequestObserver extends AbstractVisitorObserver
{
    /**
     * saveByRequest
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->visitor->saveByRequest($observer);
    }
}
