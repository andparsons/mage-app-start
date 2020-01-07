<?php

namespace Magento\NegotiableQuote\Model\Webapi\Checkout;

use Magento\NegotiableQuote\Api\PaymentInformationManagementInterface;

/**
 * Class PaymentInformationManagement
 */
class PaymentInformationManagement implements PaymentInformationManagementInterface
{
    /**
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator
     */
    private $validator;

    /**
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     */
    public function __construct(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $this->validator->validate($cartId);
        return $this->originalInterface->savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAddress);
    }

    /**
     * {@inheritdoc}
     */
    public function savePaymentInformation(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $this->validator->validate($cartId);
        return $this->originalInterface->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentInformation($cartId)
    {
        $this->validator->validate($cartId);
        return $this->originalInterface->getPaymentInformation($cartId);
    }
}
