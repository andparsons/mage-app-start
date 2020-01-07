<?php
namespace Magento\GiftCardAccount\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuoteMergeAfter implements ObserverInterface
{
    /**
     * Set the source gift card accounts into new quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getGiftCards()) {
            $quote->setGiftCards($source->getGiftCards());
        }
    }
}
