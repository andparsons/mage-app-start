<?php

namespace Magento\VisualMerchandiser\Model\Sorting\Name;

use \Magento\VisualMerchandiser\Model\Sorting\AttributeAbstract;

class Descending extends AttributeAbstract
{
    /**
     * @return string
     */
    protected function getSortField()
    {
        return 'name';
    }

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
        return __("Name: Z - A");
    }
}
