<?php

namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Framework\DB\GenericMapper;

/**
 * Class StockCriteriaMapper
 * @package Magento\CatalogInventory\Model\ResourceModel\Stock
 */
class StockCriteriaMapper extends GenericMapper
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->initResource(\Magento\CatalogInventory\Model\ResourceModel\Stock::class);
    }
}
