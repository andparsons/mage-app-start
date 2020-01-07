<?php

namespace Magento\VisualMerchandiser\Model\Sorting;

/**
 * Interface SortInterface
 * @package Magento\VisualMerchandiser\Model\Sorting
 * @api
 * @since 100.0.2
 */
interface SortInterface
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function sort(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    );

    /**
     * @return string
     */
    public function getLabel();
}
