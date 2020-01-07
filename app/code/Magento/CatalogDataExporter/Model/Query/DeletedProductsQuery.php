<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Query;

use Magento\Framework\App\ResourceConnection;

/**
 * Class DeletedProductsQuery
 */
class DeletedProductsQuery
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * MainProductQuery constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param string $mainTable
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        string $mainTable = 'catalog_product_entity'
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->mainTable = $mainTable;
    }

    /**
     * Get resource table
     *
     * @param string $tableName
     * @return string
     */
    private function getTable(string $tableName) : string
    {
        return $this->resourceConnection->getTableName($tableName);
    }

    /**
     * Update is_deleted column of catalog_data_exporter_products
     *
     * @return void
     */
    public function updateDeletedFlagQuery() : void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->joinLeft(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'cdep.sku = cpe.sku',
                ['is_deleted' => new \Zend_Db_Expr('1')]
            )
            ->where('cpe.sku IS NULL');
        $update = $connection->updateFromSelect($select, ['cdep' => $this->getTable('catalog_data_exporter_products')]);
        $connection->query($update);
    }
}
