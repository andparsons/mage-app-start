<?php
namespace Magento\Cybersource\Gateway\Request\SilentOrder;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;

/**
 * Class BillingAddressDataBuilder
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class BillingAddressDataBuilder extends AbstractAddressDataBuilder
{
    const FIELD_SUFFIX = 'bill_';

    /**
     * Returns address object from order
     *
     * @param OrderAdapterInterface $order
     * @return AddressAdapterInterface|null
     */
    protected function getAddress(OrderAdapterInterface $order)
    {
        return $order->getBillingAddress();
    }

    /**
     * Returns fields suffix
     *
     * @return string
     */
    protected function getFieldSuffix()
    {
        return self::FIELD_SUFFIX;
    }
}
