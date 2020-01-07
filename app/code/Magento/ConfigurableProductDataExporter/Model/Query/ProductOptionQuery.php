<?php
declare(strict_types=1);

namespace Magento\ConfigurableProductDataExporter\Model\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

/**
 * Class ProductOptionQuery
 */
class ProductOptionQuery
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ProductOptionQuery constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
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
     * Get resource table for attribute
     *
     * @param string $tableName
     * @param string $type
     * @return string
     */
    private function getAttributeTable(string $tableName, string $type) : string
    {
        return $this->resourceConnection->getTableName([$tableName, $type]);
    }

    /**
     * Get query for provider
     *
     * @param array $arguments
     * @return Select
     */
    public function getQuery(array $arguments): Select
    {
        $productIds = isset($arguments['productId']) ? $arguments['productId'] : [];
        $storeViewCodes = isset($arguments['storeViewCode']) ? $arguments['storeViewCode'] : [];
        $connection = $this->resourceConnection->getConnection();
        $joinField = $connection->getAutoIncrementField($this->getTable('catalog_product_entity'));
        $select = $connection->select()
            ->from(
                ['cpe' => $this->getTable('catalog_product_entity')],
                ['productId' => 'cpe.entity_id']
            )
            ->join(
                ['s' => $this->getTable('store')],
                's.store_id != 0',
                ['storeViewCode' => 's.code']
            )
            ->join(
                ['psa' => $this->getTable('catalog_product_super_attribute')],
                sprintf('psa.product_id = cpe.%s', $joinField),
                ['attribute_id' => 'psa.attribute_id']
            )
            ->joinLeft(
                ['ald' => $this->getTable('catalog_product_super_attribute_label')],
                'psa.product_super_attribute_id = ald.product_super_attribute_id and ald.store_id = 0',
                []
            )
            ->joinLeft(
                ['als' => $this->getTable('catalog_product_super_attribute_label')],
                'psa.product_super_attribute_id = als.product_super_attribute_id and als.store_id = s.store_id',
                [
                    'title' => new Expression('CASE WHEN als.value IS NULL THEN ald.value ELSE als.value END'),
                    'id' => new Expression('CASE WHEN als.value_id IS NULL THEN ald.value_id ELSE als.value_id END')
                ]
            )
            ->join(
                ['psl' => $this->getTable('catalog_product_super_link')],
                sprintf('psl.parent_id = cpe.%1$s', $joinField),
                []
            )
            ->join(
                ['cpc' => $this->getTable('catalog_product_entity')],
                'cpc.entity_id = psl.product_id'
            )
            ->join(
                ['cpi' => $this->getAttributeTable('catalog_product_entity', 'int')],
                sprintf(
                    'cpi.%1$s = cpc.%1$s AND psa.attribute_id = cpi.attribute_id AND cpi.store_id = 0',
                    $joinField
                ),
                [
                    'cpi.value'
                ]
            )
            ->where('s.code IN (?)', $storeViewCodes)
            ->where('cpe.entity_id IN (?)', $productIds);
        return $select;
    }
}
