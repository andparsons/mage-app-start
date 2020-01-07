<?php

namespace Magento\NegotiableQuote\Model;

class CheckoutQuoteValidator
{
    /**
     * Count quote items with invalid quantity
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return int
     */
    public function countInvalidQtyItems(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $count = 0;
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            if ($this->validateQuoteItemQty($item)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Validate quote item quantity
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return bool
     */
    protected function validateQuoteItemQty(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $itemErrors = $item->getErrorInfos();
        foreach ($itemErrors as $itemError) {
            if ($itemError['origin'] === 'cataloginventory'
                && $itemError['code'] === \Magento\CatalogInventory\Helper\Data::ERROR_QTY) {
                return true;
            }
        }

        return false;
    }
}
