<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\PrintQuote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class Negotiation
 *
 * @api
 * @since 100.0.0
 */
class Negotiation extends \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals\Negotiation
{
    /**
     * Values for total options
     *
     * @return $this
     */
    protected function prepareValuesForOptions()
    {
        $total = $this->getTotal();
        $percentageDiscount = 0;
        $amountDiscount = 0;
        $proposedTotal = 0;
        $catalogTotalPrice = $this->getCatalogPrice();
        if ($total->getValue() !== null) {
            switch ($total->getType()) {
                case NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT:
                    $percentageDiscount = $total->getValue();
                    $amountDiscount = $catalogTotalPrice * $total->getValue() / 100;
                    $proposedTotal = $catalogTotalPrice - $amountDiscount;
                    break;
                case NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT:
                    $amountDiscount = $total->getValue();
                    $percentageDiscount = $amountDiscount * 100 / $catalogTotalPrice;
                    $proposedTotal = $catalogTotalPrice - $amountDiscount;
                    break;
                case NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL:
                    $proposedTotal = $total->getValue();
                    $amountDiscount = $catalogTotalPrice - $total->getValue();
                    $percentageDiscount = ($catalogTotalPrice - $total->getValue()) * 100 / $catalogTotalPrice;
                    break;
            }
        }

        $this->options['percentage']['value'] = $percentageDiscount;
        $this->options['amount']['value'] = $amountDiscount;
        $this->options['proposed']['value'] = $proposedTotal;

        return $this;
    }
}
