<?php

namespace Magento\Catalog\Model\ProductLink\CollectionProvider;

class Related implements \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product)
    {
        return $product->getRelatedProducts();
    }
}
