<?php
namespace Magento\Sales\Model\ResourceModel\Provider;

/**
 * Interface provides entities id list that should be updated in grid
 */
interface NotSyncedDataProviderInterface
{
    /**
     * Returns id list of entities for adding or updating in grid.
     *
     * @param string $mainTableName source table name
     * @param string $gridTableName grid table name
     * @return array
     */
    public function getIds($mainTableName, $gridTableName);
}
