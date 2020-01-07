<?php

namespace Magento\Catalog\Model\ProductLink\CollectionProvider;

class Crosssell implements \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product)
    {
        return $product->getCrossSellProducts();
    }
}
