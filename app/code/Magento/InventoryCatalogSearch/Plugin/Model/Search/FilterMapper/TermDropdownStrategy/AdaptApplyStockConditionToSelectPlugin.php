<?php
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\ApplyStockConditionToSelect;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adapt apply stock condition to multi stocks
 */
class AdaptApplyStockConditionToSelectPlugin
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @param ApplyStockConditionToSelect $applyStockConditionToSelect
     * @param callable $proceed
     * @param string $alias
     * @param string $stockAlias
     * @param Select $select
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ApplyStockConditionToSelect $applyStockConditionToSelect,
        callable $proceed,
        string $alias,
        string $stockAlias,
        Select $select
    ) {
        $select->joinInner(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf('product.entity_id = %s.source_id', $alias),
            []
        );
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $tableName = $this->stockIndexTableNameResolver->execute((int)$stock->getStockId());

        $select->joinInner(
            [$stockAlias => $tableName],
            sprintf('product.sku = %s.sku', $stockAlias),
            []
        );
    }
}
