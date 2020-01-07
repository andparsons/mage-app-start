<?php
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Company;

/**
 * UI component for SharedCatalog ID column at shared catalog companies grid.
 */
class SharedCatalogId extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Apply sorting by shared catalog name instead of shared catalog ID.
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
                'shared_catalog_name',
                strtoupper($sorting['direction'])
            );
        }
    }
}
