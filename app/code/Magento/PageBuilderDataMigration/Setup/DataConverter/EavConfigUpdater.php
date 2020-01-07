<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Update BlueFoot EAV configuration
 *
 * @api
 */
class EavConfigUpdater
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    private $cacheManager;

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     */
    public function __construct(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        \Magento\Framework\App\Cache\Manager $cacheManager
    ) {
        $this->connection = $connection;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Update various EAV tables to connect content types to their respected attributes
     */
    public function update()
    {
        $this->connection->update(
            $this->connection->getTableName('eav_entity_type'),
            [
                'entity_model' => \Magento\PageBuilderDataMigration\Model\ResourceModel\Entity::class,
                'attribute_model' => \Magento\PageBuilderDataMigration\Model\Attribute::class,
                'entity_attribute_collection' =>
                    \Magento\PageBuilderDataMigration\Model\ResourceModel\Attribute\Collection::class
            ],
            $this->connection->quoteInto('entity_type_code = ?', 'gene_bluefoot_entity')
        );

        $entityTypeIdSelect = $this->connection->select()
            ->from($this->connection->getTableName('eav_entity_type'), ['entity_type_id'])
            ->where('entity_type_code = ?', 'gene_bluefoot_entity');
        $entityTypeId = $this->connection->fetchOne($entityTypeIdSelect);

        $attributeIdsSelect = $this->connection->select()
            ->from($this->connection->getTableName('eav_attribute'), ['attribute_id'])
            ->where(
                'attribute_code IN (?)',
                [
                    'block_id',
                    'category_id',
                    'product_id',
                    'map',
                    'video_url'
                ]
            )
            ->where('entity_type_id = ?', $entityTypeId);
        $this->connection->update(
            $this->connection->getTableName('gene_bluefoot_eav_attribute'),
            [
                'data_model' => new \Zend_Db_Expr('NULL')
            ],
            $this->connection->quoteInto('attribute_id IN (?)', $this->connection->fetchCol($attributeIdsSelect))
        );

        $attributeIdsSelect = $this->connection->select()
            ->from($this->connection->getTableName('eav_attribute'), ['attribute_id'])
            ->where(
                'attribute_code IN (?)',
                [
                    'advanced_slider_items',
                    'button_items',
                    'slider_items',
                    'accordion_items',
                    'tabs_items'
                ]
            )
            ->where('entity_type_id = ?', $entityTypeId);
        $this->connection->update(
            $this->connection->getTableName('eav_attribute'),
            [
                'source_model' => new \Zend_Db_Expr('NULL')
            ],
            $this->connection->quoteInto('attribute_id IN (?)', $this->connection->fetchCol($attributeIdsSelect))
        );

        // Flush cache to ensure new classes are used for migration
        $this->cacheManager->flush([\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER]);
    }
}
