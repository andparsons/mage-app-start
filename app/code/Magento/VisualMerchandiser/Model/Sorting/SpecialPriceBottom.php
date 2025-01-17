<?php

namespace Magento\VisualMerchandiser\Model\Sorting;

use \Magento\Framework\DB\Select;
use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class SpecialPriceBottom extends SortAbstract implements SortInterface
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function sort(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        $this->addPriceData($collection);
        $collection->getSelect()
            ->distinct('entity_id')
            ->reset(Select::ORDER)
            ->order('special_price ' . Collection::SORT_ORDER_ASC);

        return $collection;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Special price to bottom");
    }
}
