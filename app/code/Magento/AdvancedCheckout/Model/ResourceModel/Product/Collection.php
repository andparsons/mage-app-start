<?php

/**
 * Product collection resource
 *
 */
namespace Magento\AdvancedCheckout\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Join Product Price Table using left-join
     *
     * @codeCoverageIgnore
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _productLimitationJoinPrice()
    {
        return $this->_productLimitationPrice(true);
    }
}
