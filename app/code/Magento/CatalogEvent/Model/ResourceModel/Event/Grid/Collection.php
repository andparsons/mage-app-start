<?php

namespace Magento\CatalogEvent\Model\ResourceModel\Event\Grid;

class Collection extends \Magento\CatalogEvent\Model\ResourceModel\Event\Collection
{
    /**
     * Add category data to collection select (name, position)
     *
     * @return \Magento\CatalogEvent\Model\ResourceModel\Event\Grid\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addCategoryData();
        return $this;
    }
}
