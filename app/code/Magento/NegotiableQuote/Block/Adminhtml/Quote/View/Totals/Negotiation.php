<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class Negotiation
 *
 * @api
 * @since 100.0.0
 */
class Negotiation extends AbstractTotals
{
    /**
     * @var string
     */
    protected $code = 'negotiation';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Retrieve options
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getTotalOptions()
    {
        if (count($this->options) == 0) {
            $this->options['percentage'] = new \Magento\Framework\DataObject(
                [
                    'code' => NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
                    'label' => __('Percentage Discount'),
                    'is_price' => false,
                    'value' => null
                ]
            );
            $this->options['amount'] = new \Magento\Framework\DataObject(
                [
                    'code' => NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT,
                    'label' => __('Amount Discount'),
                    'is_price' => true,
                    'value' => null
                ]
            );
            $this->options['proposed'] = new \Magento\Framework\DataObject(
                [
                    'code' => NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL,
                    'label' => __('Proposed Price'),
                    'is_price' => true,
                    'value' => null
                ]
            );
        }

        $this->prepareValuesForOptions();

        return $this->options;
    }

    /**
     * Values for total options
     *
     * @return $this
     */
    protected function prepareValuesForOptions()
    {
        $total = $this->getTotal();
        foreach ($this->options as $option) {
            if ($option->getCode() == $total->getType()) {
                $option->setValue($total->getValue());
            }
        }

        return $this;
    }

    /**
     * Get catalog price value
     *
     * @return float
     */
    public function getCatalogPrice()
    {
        $totals = $this->getParentBlock()->getTotals();
        $price = 0;
        if (!empty($totals['catalog_price'])) {
            $price = $totals['catalog_price']->getValue();
        }
        return $price;
    }
}
