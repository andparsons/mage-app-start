<?php
namespace Magento\CompanyCredit\Plugin\Company\Model\ResourceModel\Company\Grid;

/**
 * Plugin for company grid collection.
 */
class CollectionPlugin
{
    /**
     * Before loadWithFilter plugin.
     *
     * @param \Magento\Company\Model\ResourceModel\Company\Grid\Collection $subject
     * @param bool $printQuery [optional]
     * @param bool $logQuery [optional]
     * @return array
     */
    public function beforeLoadWithFilter(
        \Magento\Company\Model\ResourceModel\Company\Grid\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        $subject->getSelect()->joinLeft(
            ['company_credit' => $subject->getTable('company_credit')],
            'company_credit.company_id = main_table.entity_id',
            ['company_credit.credit_limit', 'company_credit.balance', 'company_credit.currency_code']
        );
        return [$printQuery, $logQuery];
    }
}
