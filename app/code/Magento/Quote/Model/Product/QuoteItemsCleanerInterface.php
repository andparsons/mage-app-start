<?php
namespace Magento\Quote\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface \Magento\Quote\Model\Product\QuoteItemsCleanerInterface
 *
 */
interface QuoteItemsCleanerInterface
{
    /**
     * @param ProductInterface $product
     * @return void
     */
    public function execute(ProductInterface $product);
}
