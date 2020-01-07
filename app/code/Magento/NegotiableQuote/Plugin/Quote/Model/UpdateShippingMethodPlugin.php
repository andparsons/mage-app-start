<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository\LoadHandler;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class for updating shipping method in negotiable quote after load.
 */
class UpdateShippingMethodPlugin
{
    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var array
     */
    private $quoteIdShippingMethodCheck = [];

    /**
     * @var array
     */
    private $shippingMethodsByQuoteId = [];

    /**
     * @var array
     */
    private $ignoreStatus = [NegotiableQuoteInterface::STATUS_ORDERED, NegotiableQuoteInterface::STATUS_CLOSED];

    /**
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     */
    public function __construct(
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
    ) {
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    /**
     * Check and update shipping method and shipping price in negotiable quote.
     *
     * @param LoadHandler $subject
     * @param CartInterface $quote
     * @return CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(LoadHandler $subject, CartInterface $quote)
    {
        if (!in_array($quote->getId(), $this->quoteIdShippingMethodCheck)
            && $quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
            && !in_array($quote->getExtensionAttributes()->getNegotiableQuote()->getStatus(), $this->ignoreStatus)
            && $quote->getExtensionAttributes()->getShippingAssignments()
        ) {
            $this->quoteIdShippingMethodCheck[] = $quote->getId();
            $this->updateShippingMethod($quote);
        }

        return $quote;
    }

    /**
     * Update shipping method and shipping price in negotiable quote if selected shipping method are not available.
     *
     * @param CartInterface $quote
     * @return void
     */
    private function updateShippingMethod(CartInterface $quote)
    {
        foreach ($quote->getExtensionAttributes()->getShippingAssignments() as $shippingAssignment) {
            $shippingMethod = $shippingAssignment->getShipping()->getMethod();
            if ($shippingMethod) {
                $methods = $this->getShippingMethodForQuote($quote->getId());
                if (!in_array($shippingMethod, $methods)) {
                    $shippingAssignment->getShipping()->setMethod('');
                    $quote->getExtensionAttributes()->getNegotiableQuote()->setShippingPrice(null);
                }
            }
        }
    }

    /**
     * Retrieve shipping method codes for quote.
     *
     * @param int $quoteId
     * @return array
     */
    private function getShippingMethodForQuote($quoteId)
    {
        if (!isset($this->shippingMethodsByQuoteId[$quoteId])) {
            $methods = $this->shippingMethodManagement->getList($quoteId);
            $methodCodes = [];
            foreach ($methods as $method) {
                $methodCodes[] = $method->getCarrierCode() . '_' . $method->getMethodCode();
            }
            $this->shippingMethodsByQuoteId[$quoteId] = $methodCodes;
        }

        return $this->shippingMethodsByQuoteId[$quoteId];
    }
}
