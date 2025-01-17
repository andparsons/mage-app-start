<?php
namespace Magento\CustomerCustomAttributes\Observer;

use Magento\Framework\Event\ObserverInterface;

class EnterpriseCustomerAddressAttributeDelete implements ObserverInterface
{
    /**
     * @var \Magento\CustomerCustomAttributes\Model\Sales\Order\AddressFactory
     */
    protected $orderAddressFactory;

    /**
     * @var \Magento\CustomerCustomAttributes\Model\Sales\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * @param \Magento\CustomerCustomAttributes\Model\Sales\Order\AddressFactory $orderAddressFactory
     * @param \Magento\CustomerCustomAttributes\Model\Sales\Quote\AddressFactory $quoteAddressFactory
     */
    public function __construct(
        \Magento\CustomerCustomAttributes\Model\Sales\Order\AddressFactory $orderAddressFactory,
        \Magento\CustomerCustomAttributes\Model\Sales\Quote\AddressFactory $quoteAddressFactory
    ) {
        $this->orderAddressFactory = $orderAddressFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * After delete observer for customer address attribute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute instanceof \Magento\Customer\Model\Attribute && !$attribute->isObjectNew()) {
            /** @var $quoteAddress \Magento\CustomerCustomAttributes\Model\Sales\Quote\Address */
            $quoteAddress = $this->quoteAddressFactory->create();
            $quoteAddress->deleteAttribute($attribute);
            /** @var $orderAddress \Magento\CustomerCustomAttributes\Model\Sales\Order\Address */
            $orderAddress = $this->orderAddressFactory->create();
            $orderAddress->deleteAttribute($attribute);
        }
        return $this;
    }
}
