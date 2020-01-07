<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

/**
 * Class AttributeMetadata
 */
class AttributeMetadata
{
    /**
     * @var
     */
    private $attributeMetadata;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * AttributeMetadata constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get select for attributes
     *
     * @param string $attributeCode
     * @return Select
     */
    private function getAttributesSelect(string $attributeCode): Select
    {
        $connection = $this->resourceConnection->getConnection();
        return $connection->select()
            ->from(['a' => $this->resourceConnection->getTableName('eav_attribute')])
            ->join(
                ['t' => $this->resourceConnection->getTableName('eav_entity_type')],
                't.entity_type_id = a.entity_type_id',
                []
            )
            ->where('t.entity_table = ?', 'catalog_product_entity')
            ->where('a.attribute_code = ?', $attributeCode);
    }

    /**
     * Get select for options
     *
     * @param string $attributeCode
     * @return Select
     */
    private function getOptionsSelect(string $attributeCode): Select
    {
        return $this->getAttributesSelect($attributeCode)
            ->joinCross(
                ['s' => $this->resourceConnection->getTableName('store')],
                ['storeViewCode' => 's.code']
            )
            ->join(
                ['o' => $this->resourceConnection->getTableName('eav_attribute_option')],
                'o.attribute_id',
                [
                    'optionId' => 'o.option_id'
                ]
            )
            ->joinLeft(
                ['vd' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                'o.option_id  = vd.option_id AND vd.store_id = 0',
                []
            )
            ->joinLeft(
                ['vs' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                'o.option_id  = vs.option_id AND vs.store_id = s.store_id',
                ['optionValue' => new Expression(
                    'CASE WHEN vs.value IS NULL THEN vd.value ELSE vs.value END'
                )
                ]
            )
            ->where('s.store_id != 0');
    }

    /**
     * Load attributes metadata
     *
     * @param string $attributeCode
     * @throws \Zend_Db_Statement_Exception
     */
    private function loadAttributesMetadata(string $attributeCode): void
    {
        /// add attributeCode
        $connection = $this->resourceConnection->getConnection();
        $cursor = $connection->query($this->getAttributesSelect($attributeCode));
        while ($row = $cursor->fetch()) {
            $this->attributeMetadata[$row['attribute_code']] = $row;
        }
        $cursor = $connection->query($this->getOptionsSelect($attributeCode));
        while ($row = $cursor->fetch()) {
            if ($row['source_model'] == 'Magento\Eav\Model\Entity\Attribute\Source\Boolean') {
                $this->attributeMetadata[$row['attribute_code']]['options'][$row['storeViewCode']][0] = 'no';
                $this->attributeMetadata[$row['attribute_code']]['options'][$row['storeViewCode']][1] = 'yes';
            } else {
                $this->attributeMetadata[$row['attribute_code']]['options'][$row['storeViewCode']][$row['optionId']] =
                    $row['optionValue'];
            }
        }
    }

    /**
     * Get metadata for an attribute code
     *
     * @param string $attributeCode
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAttributeMetadata(string $attributeCode): array
    {
        return $this->getAttributesMetadata($attributeCode);
    }

    /**
     * Get options for attributes code
     *
     * @param string $attributeCode
     * @param string $storeView
     * @return mixed|null
     * @throws \Zend_Db_Statement_Exception
     */
    public function getOptions(string $attributeCode, string $storeView)
    {
        $attribute = $this->getAttributeMetadata($attributeCode);
        $options = null;
        if (isset($attribute['options'])) {
            $options = $attribute['options'][$storeView];
        }
        return $options;
    }

    /**
     * Check for options for attribute code
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function hasOptions(string $attributeCode): bool
    {
        $attribute = $this->getAttributeMetadata($attributeCode);
        return isset($attribute['options']);
    }

    /**
     * Get metadata for attributes
     *
     * @param string $attributeCode
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAttributesMetadata(string $attributeCode): array
    {
        if (empty($this->attributeMetadata[$attributeCode])) {
            $this->loadAttributesMetadata($attributeCode);
        }
        return $this->attributeMetadata[$attributeCode];
    }
}
