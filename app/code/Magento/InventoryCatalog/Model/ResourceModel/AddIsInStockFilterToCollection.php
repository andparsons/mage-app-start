<?php
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Adapt adding is in stock filter to collection for Multi Stocks.
 */
class AddIsInStockFilterToCollection
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableProvider;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableProvider
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableProvider
    ) {
        $this->stockIndexTableProvider = $stockIndexTableProvider;
    }

    /**
     * @param Collection $collection
     * @param int $stockId
     * @return void
     */
    public function execute($collection, int $stockId)
    {
        $tableName = $this->stockIndexTableProvider->execute($stockId);

        $collection->getSelect()->join(
            ['stock_status_index' => $tableName],
            'e.sku = stock_status_index.sku',
            []
        )->where('stock_status_index.' . IndexStructure::IS_SALABLE . ' = ?', 1);
    }
}
