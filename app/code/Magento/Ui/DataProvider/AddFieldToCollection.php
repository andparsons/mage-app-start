<?php
namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * Class AddFieldToCollection
 */
class AddFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->addFieldToSelect($field, $alias);
    }
}
