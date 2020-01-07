<?php

namespace Magento\SharedCatalog\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Data\Collection;

/**
 * Class that implements product filtering by shared catalog name on catalog products grid.
 */
class AddSharedCatalogFilterToCollection implements \Magento\Ui\DataProvider\AddFilterToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (is_array($condition) && isset($condition['in'])) {
            $collection->getSelect()->joinInner(
                ['scpi' => $collection->getTable('shared_catalog_product_item')],
                'scpi.sku=e.sku',
                []
            );
            $collection->getSelect()->joinInner(
                ['sc' => $collection->getTable('shared_catalog')],
                'sc.customer_group_id=scpi.customer_group_id',
                []
            )->where('sc.entity_id IN (?)', $condition['in']);
            $collection->distinct(true);
        }
    }
}
