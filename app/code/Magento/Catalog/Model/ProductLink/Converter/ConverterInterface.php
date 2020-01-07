<?php

namespace Magento\Catalog\Model\ProductLink\Converter;

/**
 * Interface \Magento\Catalog\Model\ProductLink\Converter\ConverterInterface
 *
 */
interface ConverterInterface
{
    /**
     * Convert product to array representation
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function convert(\Magento\Catalog\Model\Product $product);
}
