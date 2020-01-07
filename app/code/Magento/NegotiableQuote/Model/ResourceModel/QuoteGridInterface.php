<?php

namespace Magento\NegotiableQuote\Model\ResourceModel;

/**
 * Interface GridInterface
 */
interface QuoteGridInterface
{
    /**
     * Adds new rows to the grid
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quoteData
     * @return $this
     */
    public function refresh(\Magento\Quote\Api\Data\CartInterface $quoteData);

    /**
     * Refresh specified values for field with condition
     *
     * @param string $updateWhereField
     * @param string $updatedWhereValue
     * @param string $value
     * @param string $field
     * @return $this
     */
    public function refreshValue($updateWhereField, $updatedWhereValue, $value, $field);

    /**
     * Remove quote from quote grid
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     */
    public function remove(\Magento\Quote\Api\Data\CartInterface $quote);
}
