<?php

namespace Magento\VisualMerchandiser\Model\Sorting\Sku;

use \Magento\VisualMerchandiser\Model\Sorting\AttributeAbstract;

class Ascending extends AttributeAbstract
{
    /**
     * @return string
     */
    protected function getSortField()
    {
        return 'sku';
    }

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
        return __("SKU: Ascending");
    }
}
