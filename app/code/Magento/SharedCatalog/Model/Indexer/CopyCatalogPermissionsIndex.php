<?php
namespace Magento\SharedCatalog\Model\Indexer;

use Magento\CatalogPermissions\Model\Indexer\UpdateIndexInterface;
use Magento\Customer\Api\Data\GroupInterface;

/**
 * Copy index data in the table from default customer group
 */
class CopyCatalogPermissionsIndex implements UpdateIndexInterface
{
    /**
     * @var CopyIndex
     */
    private $copyIndex;

    /**
     * Constructor
     *
     * @param \Magento\SharedCatalog\Model\Indexer\CopyIndex $copyIndex
     */
    public function __construct(
        \Magento\SharedCatalog\Model\Indexer\CopyIndex $copyIndex
    ) {
        $this->copyIndex = $copyIndex;
    }

    /**
     * {@inheritdoc}
     */
    public function update(GroupInterface $group, $isGroupNew)
    {
        if (!$isGroupNew) {
            return;
        }
        $this->copyIndex->copy(
            $group,
            [
                'magento_catalogpermissions_index',
                'magento_catalogpermissions_index_product'
            ]
        );
    }
}
