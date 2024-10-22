<?php
namespace Magento\ConfigurableProduct\Pricing\Render;

use Magento\Catalog\Pricing\Price\TierPrice;

/**
 * Responsible for displaying tier price box on configurable product page.
 *
 * @package Magento\ConfigurableProduct\Pricing\Render
 */
class TierPriceBox extends FinalPriceBox
{
    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        // Hide tier price block in case of MSRP or in case when no options with tier price.
        if (!$this->isMsrpPriceApplicable() && $this->isTierPriceApplicable()) {
            return parent::toHtml();
        }
    }

    /**
     * Check if at least one of simple products has tier price.
     *
     * @return bool
     */
    private function isTierPriceApplicable()
    {
        $product = $this->getSaleableItem();
        foreach ($product->getTypeInstance()->getUsedProducts($product) as $simpleProduct) {
            if ($simpleProduct->isSalable() &&
                !empty($simpleProduct->getPriceInfo()->getPrice(TierPrice::PRICE_CODE)->getTierPriceList())
            ) {
                return true;
            }
        }
        return false;
    }
}
