<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model;

/**
 * Class ProductPlugin
 *
 * Plugin for support basic quote cleaner implementation.
 */
class QuoteItemPlugin
{
    /**
     * @var QuoteRecalculate
     */
    private $quoteRecalculate;

    /**
     * @param QuoteRecalculate $quoteRecalculate
     */
    public function __construct(\Magento\NegotiableQuote\Plugin\Quote\Model\QuoteRecalculate $quoteRecalculate)
    {
        $this->quoteRecalculate = $quoteRecalculate;
    }

    /**
     * Plugin around execute
     *
     * @param \Magento\Quote\Model\Product\QuoteItemsCleanerInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Quote\Model\Product\QuoteItemsCleanerInterface $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        return $this->quoteRecalculate->updateQuotesByProduct($proceed, $product);
    }
}
