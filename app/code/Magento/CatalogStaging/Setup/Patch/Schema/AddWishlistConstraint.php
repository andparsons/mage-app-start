<?php
declare(strict_types=1);

namespace Magento\CatalogStaging\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class AddProductIdConstraint
 */
class AddWishlistConstraint implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * Run code inside patch.
     *
     * @return void
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();

        $this->schemaSetup->getConnection()->dropForeignKey(
            $this->schemaSetup->getTable('wishlist_item_option'),
            $this->schemaSetup->getConnection()->getForeignKeyName(
                $this->schemaSetup->getTable('wishlist_item_option'),
                'product_id',
                $this->schemaSetup->getTable('catalog_product_entity'),
                'entity_id'
            )
        );

        $this->schemaSetup->getConnection()->addForeignKey(
            $this->schemaSetup->getConnection()->getForeignKeyName(
                $this->schemaSetup->getTable('wishlist_item_option'),
                'product_id',
                $this->schemaSetup->getTable('sequence_product'),
                'sequence_value'
            ),
            $this->schemaSetup->getTable('wishlist_item_option'),
            'product_id',
            $this->schemaSetup->getTable('sequence_product'),
            'sequence_value',
            AdapterInterface::FK_ACTION_CASCADE,
            true
        );

        $this->schemaSetup->endSetup();
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }
}
