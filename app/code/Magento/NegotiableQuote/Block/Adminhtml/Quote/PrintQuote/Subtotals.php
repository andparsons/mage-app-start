<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\PrintQuote;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\NegotiableQuote\Model\Quote\TotalsFactory;

/**
 * Class Subtotals.
 *
 * @api
 * @since 100.0.0
 */
class Subtotals extends \Magento\NegotiableQuote\Block\Quote\Totals
{
    /**
     * Initialize quote totals array.
     *
     * @return $this
     */
    protected function initTotals()
    {
        $this->quoteTotals = $this->quoteTotalsFactory->create(['quote' => $this->getCollectedQuote()]);
        $this->initSubtotal();

        return $this;
    }
}
