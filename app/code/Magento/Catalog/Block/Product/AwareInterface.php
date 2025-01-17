<?php
namespace Magento\Catalog\Block\Product;

/**
 * Class Product Aware interface
 */
interface AwareInterface
{
    /**
     * Set product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Api\Data\ProductInterface $product);
}
