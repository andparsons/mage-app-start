<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model;

/**
 * Class QuoteItemConsumerPlugin
 *
 * Plugin for support enterprise quote cleaner implementation using queue.
 */
class QuoteItemConsumerPlugin
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
     * Plugin around process message.
     *
     * @param \Magento\ScalableCheckout\Model\ResourceModel\Quote\Item\Consumer $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed
     */
    public function aroundProcessMessage(
        \Magento\ScalableCheckout\Model\ResourceModel\Quote\Item\Consumer $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        return $this->quoteRecalculate->updateQuotesByProduct($proceed, $product);
    }
}
