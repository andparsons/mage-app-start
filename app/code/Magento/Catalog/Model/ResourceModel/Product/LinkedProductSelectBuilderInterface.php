<?php
namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Provide Select object for retrieve product id with minimal price
 */
interface LinkedProductSelectBuilderInterface
{
    /**
     * @param int $productId
     * @return \Magento\Framework\DB\Select[]
     */
    public function build($productId);
}
