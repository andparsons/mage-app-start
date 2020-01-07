<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Collection\Grid;

use Magento\Company\Model\ResourceModel\Company\Grid\Collection as CompanyCollection;

/**
 * Company grid collection. Provides data for shared catalog companies grid.
 */
class Company extends CompanyCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinSharedCatalogTable();
        return $this;
    }

    /**
     * Add collection filters by identifiers.
     *
     * @param array $companyIds
     * @param bool $exclude [optional]
     * @return $this
     */
    public function addIdFilter(array $companyIds, $exclude = false)
    {
        if ($exclude && empty($companyIds)) {
            return $this;
        }

        if ($exclude) {
            $condition = ['nin' => $companyIds];
        } else {
            $condition = ['in' => $companyIds];
        }
        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    /**
     * Join shared catalog table.
     *
     * @return $this
     */
    private function joinSharedCatalogTable()
    {
        $this->getSelect()->joinLeft(
            ['shared_catalog' => $this->getTable('shared_catalog')],
            'main_table.customer_group_id = shared_catalog.customer_group_id',
            [
                'shared_catalog_id' => 'shared_catalog.entity_id',
                'shared_catalog_name' => 'shared_catalog.name'
            ]
        );

        return $this;
    }

    /**
     * Add is_current column to collection.
     *
     * The column is used to indicate if a company is assigned to current shared catalog.
     *
     * @param int $currentSharedCatalogId
     * @return $this
     */
    public function addIsCurrentColumn($currentSharedCatalogId)
    {
        $this->getSelect()->joinLeft(
            ['shared_catalog_is_current' => $this->getTable('shared_catalog')],
            'main_table.customer_group_id = shared_catalog_is_current.customer_group_id',
            [
                'is_current' => 'IF(shared_catalog_is_current.entity_id = ' . $currentSharedCatalogId . ', 0, 1)'
            ]
        );

        return $this;
    }
}
