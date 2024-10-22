<?php
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Indexer\SourceItem;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfigurableProductIndexer\Indexer\SelectBuilder;
use Magento\InventoryIndexer\Indexer\IndexStructure;

/**
 * Returns all data for the index by source item list condition
 */
class IndexDataBySkuListProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SelectBuilder $selectBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SelectBuilder $selectBuilder
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->selectBuilder = $selectBuilder;
    }

    /**
     * @param int $stockId
     * @param array $skuList
     * @return ArrayIterator
     */
    public function execute(int $stockId, array $skuList): ArrayIterator
    {
        $select = $this->selectBuilder->execute($stockId);

        if (count($skuList)) {
            $select->where('stock.' . IndexStructure::SKU . ' IN (?)', $skuList);
        }

        $connection = $this->resourceConnection->getConnection();
        return new ArrayIterator($connection->fetchAll($select));
    }
}
