<?php
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Interface TableDataInterface
 */
interface TableDataInterface
{
    /**
     * Move data from temporary tables to flat
     *
     * @param string $flatTable
     * @param string $flatDropName
     * @param string $temporaryFlatTableName
     * @return void
     */
    public function move($flatTable, $flatDropName, $temporaryFlatTableName);
}
