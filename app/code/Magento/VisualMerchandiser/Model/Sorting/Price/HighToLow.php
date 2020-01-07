<?php

namespace Magento\VisualMerchandiser\Model\Sorting\Price;

use \Magento\VisualMerchandiser\Model\Sorting\PriceAbstract;

class HighToLow extends PriceAbstract
{
    /**
     * @return string
     */
    protected function getSortDirection()
    {
        return $this->descOrder();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Price: High to low');
    }
}
