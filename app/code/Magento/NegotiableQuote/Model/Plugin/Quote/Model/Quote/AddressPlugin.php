<?php

namespace Magento\NegotiableQuote\Model\Plugin\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address;
use Magento\Framework\App\State;

/**
 * Plugin for class Address to apply negotiations shipping price and shipping method on shipping information.
 */
class AddressPlugin
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param State $appState
     */
    public function __construct(State $appState)
    {
        $this->appState = $appState;
    }

    /**
     * Set shipping price and shipping method after address request shipping rates.
     *
     * @param Address $address
     * @param bool $result
     * @return bool
     */
    public function afterRequestShippingRates(Address $address, bool $result): bool
    {
        if (!$result) {
            return $result;
        }
        $quote = $address->getQuote();
        $negotiatedShippingMethod = $address->getShippingMethod();

        if (!$this->allowChangeShippingAddress($quote, $negotiatedShippingMethod, $result)) {
            return $result;
        }
        $negotiatedShippingPrice = $quote->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice();

        foreach ($address->getAllShippingRates() as $rate) {
            $rate->setData('original_price', $rate->getPrice());
            /** @var $rate \Magento\Quote\Model\Quote\Address\Rate */
            if (($rate->getCode() == $negotiatedShippingMethod) && $negotiatedShippingPrice) {
                $rate->setPrice($negotiatedShippingPrice);
            }
            if ((\Magento\Framework\App\Area::AREA_ADMINHTML !== $this->appState->getAreaCode())
                && ($rate->getCode() != $negotiatedShippingMethod)
            ) {
                $rate->isDeleted(true);
            }
        }
        $address->setShippingAmount($negotiatedShippingPrice)->setBaseShippingAmount($negotiatedShippingPrice);

        return $result;
    }

    /**
     * Checks if shipping address changes are allowed.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $negotiatedShippingMethod
     * @param bool $result
     * @return bool
     */
    private function allowChangeShippingAddress(
        \Magento\Quote\Model\Quote $quote,
        string $negotiatedShippingMethod,
        bool $result
    ): bool {
        if (!$result
            || !($quote->getExtensionAttributes()
                && $quote->getExtensionAttributes()->getNegotiableQuote()
                && $quote->getExtensionAttributes()->getNegotiableQuote()->getId())
            || empty($negotiatedShippingMethod)
        ) {
            return false;
        }

        return true;
    }
}
