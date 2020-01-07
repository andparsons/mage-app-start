<?php
namespace Magento\SharedCatalog\Ui\Component\Company\Listing\Columns;

/**
 * UI component for Customer Group ID column at companies grid.
 */
class CustomerGroupId extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Apply sorting by customer_group_code instead of customer_group_id.
     *
     * @return void
     */
    public function applySorting()
    {
        $sorting = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === $this->getName()
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                'customer_group_code',
                strtoupper($sorting['direction'])
            );
        }
    }
}
