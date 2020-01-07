<?php
namespace Magento\Company\Plugin\Customer\Model\ResourceModel\Grid;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Customer\Model\ResourceModel\Grid\Collection;
use Magento\Framework\DB\Select;

/**
 * Plugin for customer grid collection.
 */
class CollectionPlugin
{
    /**
     * @var string
     */
    private $customerTypeExpressionPattern = '(IF(company_customer.company_id > 0, '
        . 'IF(company_customer.customer_id = company.super_user_id, "%d", "%d"), "%d"))';

    /**
     * @var array
     */
    private $expressionFields = [
        'customer_type'
    ];

    /**
     * Before loadWithFilter plugin.
     *
     * @param Collection $subject
     * @param bool $printQuery [optional]
     * @param bool $logQuery [optional]
     * @return array
     */
    public function beforeLoadWithFilter(
        Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        foreach ($this->getAdditionalTables() as $tableName => $tableParam) {
            $this->joinAdditionalTable(
                $subject->getSelect(),
                $subject->getTable($tableName),
                $tableParam['alias'],
                $tableParam['condition'],
                $tableParam['columns']
            );
        }

        return [$printQuery, $logQuery];
    }

    /**
     * Around addFieldToFilter plugin.
     *
     * @param Collection $subject
     * @param \Closure $proceed
     * @param string|array $field
     * @param null|string|array $condition [optional]
     * @return Collection
     */
    public function aroundAddFieldToFilter(
        Collection $subject,
        \Closure $proceed,
        $field,
        $condition = null
    ) {
        $fieldMap = $this->getFilterFieldsMap();
        $fieldName = $fieldMap['fields'][$field] ?? null;
        if (!$fieldName) {
            return $proceed($field, $condition);
        }

        foreach ($this->getAdditionalTables() as $tableName => $tableParam) {
            $this->joinAdditionalTable(
                $subject->getSelect(),
                $subject->getTable($tableName),
                $tableParam['alias'],
                $tableParam['condition'],
                $tableParam['columns']
            );
        }

        if (!\in_array($field, $this->expressionFields, true)) {
            $fieldName = $subject->getConnection()->quoteIdentifier($fieldName);
        }
        $condition = $subject->getConnection()->prepareSqlCondition($fieldName, $condition);
        $subject->getSelect()->where($condition, null, Select::TYPE_CONDITION);

        return $subject;
    }

    /**
     * Get map for filterable fields.
     *
     * @return array
     */
    private function getFilterFieldsMap()
    {
        return [
            'fields' => [
                'email' => 'main_table.email',
                'status' => 'company_customer.status',
                'customer_type' => $this->prepareCustomerTypeColumnExpression()
            ]
        ];
    }

    /**
     * Prepare expression for customer type column.
     *
     * @return string
     */
    private function prepareCustomerTypeColumnExpression()
    {
        return sprintf(
            $this->customerTypeExpressionPattern,
            CompanyCustomerInterface::TYPE_COMPANY_ADMIN,
            CompanyCustomerInterface::TYPE_COMPANY_USER,
            CompanyCustomerInterface::TYPE_INDIVIDUAL_USER
        );
    }

    /**
     * Get additional tables.
     *
     * @return array
     */
    private function getAdditionalTables(): array
    {
        return [
            'company_advanced_customer_entity' => [
                'alias' => 'company_customer',
                'condition' => 'company_customer.customer_id = main_table.entity_id',
                'columns' => [
                    'company_customer.status',
                ],
            ],
            'company' => [
                'alias' => 'company',
                'condition' => 'company.entity_id = company_customer.company_id',
                'columns' => [
                    'company_name' => 'company.company_name',
                    'customer_type' => new \Zend_Db_Expr($this->prepareCustomerTypeColumnExpression()),
                ],
            ],
        ];
    }

    /**
     * Join additional table to select.
     *
     * @param Select $select
     * @param string $tableName
     * @param string $tableAlias
     * @param string $condition
     * @param array $columns
     * @return void
     */
    private function joinAdditionalTable(
        Select $select,
        string $tableName,
        string $tableAlias,
        string $condition,
        array $columns
    ) {
        $usedTables = array_column($select->getPart(Select::FROM), 'tableName');
        if (!\in_array($tableName, $usedTables, true)) {
            $select->joinLeft([$tableAlias => $tableName], $condition, $columns);
        }
    }
}
