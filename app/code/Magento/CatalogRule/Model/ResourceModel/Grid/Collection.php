<?php
namespace Magento\CatalogRule\Model\ResourceModel\Grid;

class Collection extends \Magento\CatalogRule\Model\ResourceModel\Rule\Collection
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addWebsitesToResult();

        return $this;
    }
}
