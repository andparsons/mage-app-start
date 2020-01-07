<?php

namespace Magento\VisualMerchandiser\Model\Sorting\Name;

use \Magento\VisualMerchandiser\Model\Sorting\AttributeAbstract;

class Ascending extends AttributeAbstract
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
        return $this->ascOrder();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Name: A - Z");
    }
}
