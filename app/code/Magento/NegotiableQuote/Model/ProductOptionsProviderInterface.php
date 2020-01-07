<?php

namespace Magento\NegotiableQuote\Model;

/**
 * Provides options data for complex products.
 */
interface ProductOptionsProviderInterface
{
    /**
     * Get options provider product type.
     *
     * @return string
     */
    public function getProductType();

    /**
     * Get list of product options.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOptions(\Magento\Catalog\Model\Product $product);
}
