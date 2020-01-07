<?php
namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * AddFilterToCollection interface
 */
interface AddFilterToCollectionInterface
{
    /**
     * @param Collection $collection
     * @param string $field
     * @param string|null $condition
     * @return void
     */
    public function addFilter(Collection $collection, $field, $condition = null);
}
