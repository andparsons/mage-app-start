<?php

namespace Magento\Catalog\Model\ProductLink;

/**
 * Interface \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
 *
 */
interface CollectionProviderInterface
{
    /**
     * Get linked products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product);
}
