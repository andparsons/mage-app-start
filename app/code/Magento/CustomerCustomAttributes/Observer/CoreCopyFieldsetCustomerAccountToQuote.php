<?php
namespace Magento\CustomerCustomAttributes\Observer;

use Magento\Framework\Event\ObserverInterface;

class CoreCopyFieldsetCustomerAccountToQuote extends AbstractObserver implements ObserverInterface
{
    /**
     * Observer for converting customer to quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_copyFieldset($observer, self::CONVERT_ALGORITM_SOURCE_WITHOUT_PREFIX, self::CONVERT_TYPE_CUSTOMER);

        return $this;
    }
}
