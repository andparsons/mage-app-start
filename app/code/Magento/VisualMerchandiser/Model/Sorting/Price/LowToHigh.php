<?php

namespace Magento\VisualMerchandiser\Model\Sorting\Price;

use \Magento\VisualMerchandiser\Model\Sorting\PriceAbstract;

class LowToHigh extends PriceAbstract
{
    /**
     * @return string
     */
    protected function getSortDirection()
    {
        return $this->ascOrder();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Price: Low to high');
    }
}