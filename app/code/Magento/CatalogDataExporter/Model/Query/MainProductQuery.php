<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Query;

use Magento\DataExporter\Sql\FieldToPropertyNameConverter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

/**
 * Class MainProductQuery
 */
class MainProductQuery
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FieldToPropertyNameConverter
     */
    private $nameConverter;

    /**
     * @var string
     */
    private $mainTable;

    /**
     * @var array
     */
    private $includeAttributes;

    /**
     * MainProductQuery constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param FieldToPropertyNameConverter $nameConverter
     * @param string $mainTable
     * @param array $includeAttributes
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        FieldToPropertyNameConverter $nameConverter,
        string $mainTable,
        array $includeAttributes = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->nameConverter = $nameConverter;
        $this->mainTable = $mainTable;
        $this->includeAttributes = $includeAttributes;
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
     * Get resource table for attributes
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
     * Get metadata for attributes
     *
     * @return array
     */
    private function getAttributesMetadata() : array
    {
        $connection = $this->resourceConnection->getConnection();
        $attributesMetadata = $connection->fetchAll(
            $connection->select()
                ->from(['a' => $this->getTable('eav_attribute')], [])
                ->join(
                    ['t' => $this->getTable('eav_entity_type')],
                    't.entity_type_id = a.entity_type_id',
                    []
                )
                ->where('a.is_user_defined  = 0')
                ->where('t.entity_table = ?', $this->mainTable)
                ->where('a.backend_type != ?', 'static')
                ->where('a.attribute_code IN (?)', $this->includeAttributes)
                ->columns(
                    [
                        'code' => 'a.attribute_code',
                        'id' => 'a.attribute_id',
                        'type' => 'a.backend_type'
                    ]
                )
        );
        return $attributesMetadata;
    }

    /**
     * Join attributes for select
     *
     * @param Select $select
     * @return Select
     */
    private function joinAttributes(Select $select) : Select
    {
        $connection = $this->resourceConnection->getConnection();
        $joinField = $connection->getAutoIncrementField($this->mainTable);
        $attributesMetadata = $this->getAttributesMetadata();
        foreach ($attributesMetadata as $attribute) {
            $defaultValueTableAlias = $attribute['code'] . 'default';
            $storeValueTableAlias = $attribute['code'] . 'store';
            $defaultValueJoinCondition = sprintf(
                '%1$s.%2$s = cpe.%2$s AND %1$s.attribute_id = %3$d AND %1$s.store_id = 0',
                $defaultValueTableAlias,
                $joinField,
                $attribute['id']
            );
            $storeViewValueJoinCondition = sprintf(
                '%1$s.%2$s = cpe.%2$s AND %1$s.attribute_id = %3$d AND %1$s.store_id = s.store_id',
                $storeValueTableAlias,
                $joinField,
                $attribute['id']
            );
            $attributeValueExpression = sprintf(
                'CASE WHEN %1$s.value IS NULL THEN %2$s.value ELSE %1$s.value END',
                $storeValueTableAlias,
                $defaultValueTableAlias
            );
            $select->joinLeft(
                [
                    $defaultValueTableAlias => $this->getAttributeTable($this->mainTable, $attribute['type'])
                ],
                $defaultValueJoinCondition,
                []
            )
            ->joinLeft(
                [
                    $storeValueTableAlias => $this->getAttributeTable($this->mainTable, $attribute['type'])
                ],
                $storeViewValueJoinCondition,
                [
                    $this->nameConverter->toCamelCase($attribute['code']) => new Expression($attributeValueExpression)
                ]
            );
        }
        return $select;
    }

    /**
     * Get query for provider
     *
     * @param array $arguments
     * @return Select
     */
    public function getQuery(array $arguments) : Select
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                ['cpe' => $this->getTable($this->mainTable)],
                [
                    'sku',
                    'productId' => 'cpe.entity_id',
                    'type' => 'cpe.type_id',
                    'createdAt' => 'cpe.created_at',
                    'updatedAt' => 'cpe.updated_at'
                ]
            )
            ->joinCross(
                ['s' => $this->getTable('store')],
                ['storeViewCode' => 's.code']
            )
            ->join(
                ['cpw' => $this->getTable('catalog_product_website')],
                'cpw.website_id = s.website_id AND cpw.product_id = cpe.entity_id',
                []
            )
            ->where('s.store_id != 0')
            ->where('cpe.entity_id IN (?)', $arguments['productId']);
        $select = $this->joinAttributes($select);
        return $select;
    }
}
