<?php
namespace Magento\CustomerBalance\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuoteMergeAfterObserver implements ObserverInterface
{
    /**
     * Set the source customer balance usage flag into new quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getUseCustomerBalance()) {
            $quote->setUseCustomerBalance($source->getUseCustomerBalance());
        }
    }
}
