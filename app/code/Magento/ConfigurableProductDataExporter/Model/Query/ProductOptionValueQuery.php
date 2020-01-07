<?php
declare(strict_types=1);

namespace Magento\ConfigurableProductDataExporter\Model\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

/**
 * Class ProductOptionVariantQuery
 */
class ProductOptionValueQuery
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ProductOptionValueQuery constructor.
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
        $subSelect = $connection->select()
            ->from(
                ['cpe' => $this->getTable('catalog_product_entity')],
                []
            )
            ->join(
                ['psa' => $this->getTable('catalog_product_super_attribute')],
                sprintf('psa.product_id = cpe.%s', $joinField),
                ['attribute_id' => 'psa.attribute_id']
            )
            ->where('cpe.entity_id IN (?)', $productIds);
        $select = $connection->select()
            ->from(
                ['eao' => $this->getTable('eav_attribute_option')],
                [
                    'attribute_id' => 'eao.attribute_id',
                    'optionId' => 'eao.option_id'
                ]
            )
            ->join(
                ['s' => $this->getTable('store')],
                's.store_id != 0',
                ['storeViewCode' => 's.code']
            )
            ->joinLeft(
                ['ovd' => $this->getTable('eav_attribute_option_value')],
                'ovd.option_id = eao.option_id AND ovd.store_id = 0',
                []
            )
            ->joinLeft(
                ['ovs' => $this->getTable('eav_attribute_option_value')],
                'ovs.option_id = eao.option_id AND ovs.store_id = s.store_id',
                [
                    'value' => new Expression('CASE WHEN ovs.value IS NULL THEN ovd.value ELSE ovs.value END'),
                    'id' => new Expression('CASE WHEN ovs.value_id IS NULL THEN ovd.value_id ELSE ovs.value_id END'),
                ]
            )
            ->where(sprintf('eao.attribute_id IN (%s)', $subSelect->assemble()))
            ->where('s.code IN (?)', $storeViewCodes);
        return $select;
    }
}
