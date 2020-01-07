<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model\Cart\Totals;

/**
 * Class ItemConverter
 */
class ItemConverterPlugin
{
    /**
     * Set extension attributes to null, because converting Item to Totals/Item with extension attributes is impossible
     * MAGETWO-50025
     *
     * @param \Magento\Quote\Model\Cart\Totals\ItemConverter $subject
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeModelToDataObject(
        \Magento\Quote\Model\Cart\Totals\ItemConverter $subject,
        \Magento\Quote\Api\Data\CartItemInterface $item
    ) {
        if ($item->getExtensionAttributes() !== null
            && $item->getExtensionAttributes()->getNegotiableQuoteItem() !== null
        ) {
            $item->setData('extension_attributes', null);
        }
    }
}
