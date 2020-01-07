<?php
namespace Magento\Company\Model\ResourceModel\Users\Grid;

/**
 * Class Collection.
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Grid\Collection
{
    /**
     * @var array
     */
    protected $mapper = [
        'status' => 'company_customer.status'
    ];

    /**
     * Init select.
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinAdvancedCustomerEntityTable();
        $this->joinCompanyTable();
        $this->joinUserRolesTable();
        $this->joinRolesTable();
        $this->setSortOrder();

        return $this;
    }

    /**
     * Add field filter to collection.
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return \Magento\Company\Model\ResourceModel\Users\Grid\Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (isset($this->mapper[$field])) {
            $field = $this->mapper[$field];
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Join advanced customer entity table.
     *
     * @return void
     */
    private function joinAdvancedCustomerEntityTable()
    {
        $this->getSelect()->joinLeft(
            ['company_customer' => $this->getTable('company_advanced_customer_entity')],
            'company_customer.customer_id = main_table.entity_id',
            ['company_customer.status']
        );
    }

    /**
     * Join company table.
     *
     * @return void
     */
    private function joinCompanyTable()
    {
        $this->getSelect()->joinLeft(
            ['company' => $this->getTable('company')],
            'company.entity_id = company_customer.company_id',
            ['company.entity_id AS company_entity_id']
        );
    }

    /**
     * Join user roles table.
     *
     * @return void
     */
    private function joinUserRolesTable()
    {
        $this->getSelect()->joinLeft(
            ['user_role' => $this->getTable('company_user_roles')],
            'user_role.user_id = main_table.entity_id',
            ['']
        );
    }

    /**
     * Join roles table name.
     *
     * @return void
     */
    private function joinRolesTable()
    {
        $this->getSelect()->joinLeft(
            ['role' => $this->getTable('company_roles')],
            'user_role.role_id = role.role_id',
            ['role.role_id', 'role.role_name']
        );
    }

    /**
     * Set sort order.
     *
     * @return void
     */
    private function setSortOrder()
    {
        $this->setOrder('main_table.name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }
}
