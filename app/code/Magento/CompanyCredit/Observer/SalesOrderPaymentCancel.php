<?php

namespace Magento\CompanyCredit\Observer;

/**
 * Perform refund when the order is canceled.
 */
class SalesOrderPaymentCancel implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $method = $observer->getPayment()->getMethodInstance();
        if ($method->getCode() == \Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME) {
            $method->cancel($observer->getPayment());
        }
    }
}
