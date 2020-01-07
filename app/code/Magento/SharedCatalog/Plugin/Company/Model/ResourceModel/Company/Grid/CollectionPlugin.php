<?php
namespace Magento\SharedCatalog\Plugin\Company\Model\ResourceModel\Company\Grid;

/**
 * Add customer_group_code column to companies grid collection.
 */
class CollectionPlugin
{
    /**
     * Add customer_group_code column to companies grid collection before loading.
     *
     * @param \Magento\Company\Model\ResourceModel\Company\Grid\Collection $subject
     * @param bool $printQuery [optional]
     * @param bool $logQuery [optional]
     * @return array
     */
    public function beforeLoad(
        \Magento\Company\Model\ResourceModel\Company\Grid\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        $subject->getSelect()
            ->joinLeft(
                ['customer_group' => $subject->getTable(
                    'customer_group'
                )],
                'main_table.customer_group_id = customer_group.customer_group_id',
                ['customer_group_code']
            );

        return [$printQuery, $logQuery];
    }
}
