<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Plugin\CatalogPermissions\Model\Indexer\Product\Action;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\CatalogPermissions\Model\Permission;
use Magento\CatalogPermissions\Model\Indexer\Product\Action\ProductSelectDataProvider;
use Magento\SharedCatalog\Api\StatusInfoInterface;
use Magento\SharedCatalog\Model\CustomerGroupManagement;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin provides additional filtration with shared catalog settings
 */
class UpdateProductSelectPermissionsPlugin
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var StatusInfoInterface
     */
    private $sharedCatalogConfig;

    /**
     * @var CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @param ResourceConnection $resource
     * @param StatusInfoInterface $sharedCatalogConfig
     * @param CustomerGroupManagement $customerGroupManagement
     */
    public function __construct(
        ResourceConnection $resource,
        StatusInfoInterface $sharedCatalogConfig,
        CustomerGroupManagement $customerGroupManagement
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->sharedCatalogConfig = $sharedCatalogConfig;
        $this->customerGroupManagement = $customerGroupManagement;
    }

    /**
     * Add shared catalog filtration based on customer group
     *
     * @param ProductSelectDataProvider $subject
     * @param Select $result
     * @param int $customerGroupId
     * @param int $storeId
     * @param array $permissionsColumns
     * @param string $indexTableName
     * @param array $productList
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelect(
        ProductSelectDataProvider $subject,
        Select $result,
        int $customerGroupId,
        int $storeId,
        array $permissionsColumns,
        string $indexTableName,
        array $productList
    ):Select {
        if ($this->sharedCatalogConfig->isActive(ScopeInterface::SCOPE_STORE, $storeId)
            && !$this->customerGroupManagement->isMasterCatalogAvailable($customerGroupId)
        ) {
            $result->joinLeft(
                ['scpi' => $this->resource->getTableName('shared_catalog_product_item')],
                'cpe.sku = scpi.sku' . $this->connection->quoteInto(
                    ' AND scpi.customer_group_id = ?',
                    $customerGroupId
                ),
                []
            );

            $columns = $result->getPart(Select::COLUMNS);
            $numColumns = count($columns);
            for ($i = 0; $i < $numColumns; $i++) {
                if ($columns[$i][2] === 'grant_catalog_category_view') {
                    $columns[$i][0] = null;
                    $columns[$i][1] = new \Zend_Db_Expr(
                        'IF ( ' .
                        $this->connection->quoteInto(' scpi.entity_id IS NULL, ?, ', Permission::PERMISSION_DENY) .
                        $columns[$i][1] . ')'
                    );
                    $result->setPart(Select::COLUMNS, $columns);
                    break;
                }
            }
        }

        return $result;
    }
}
