<?php

namespace Magento\SharedCatalog\Model\Customer\Source\Collection;

use Magento\Customer\Api\Data\GroupInterface;

/**
 * Join shared catalog table to the customer group collection.
 */
class Group extends \Magento\Customer\Model\ResourceModel\Group\Collection
{
    /**
     * Join shared catalog table to the customer group collection.
     *
     * @param bool $catalogGroups [optional]
     * @param bool $excludeNotLogged [optional]
     * @return $this
     */
    public function joinSharedCatalogTable($catalogGroups = false, $excludeNotLogged = true)
    {
        if ($excludeNotLogged) {
            $this->addFieldToFilter('main_table.customer_group_id', ['neq' => GroupInterface::NOT_LOGGED_IN_ID]);
        }
        $this->getSelect()->joinLeft(
            ['shared_catalog' => $this->getTable('shared_catalog')],
            'main_table.customer_group_id = shared_catalog.customer_group_id',
            [
                'shared_catalog_id' => 'shared_catalog.entity_id',
                'shared_catalog_name' => 'shared_catalog.name'
            ]
        );
        $this->getSelect()->where('shared_catalog.entity_id IS' . ($catalogGroups ? ' NOT' : '') . ' NULL');
        $this->addOrder('main_table.customer_group_code', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        return $this;
    }
}
