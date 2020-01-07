<?php
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

/**
 * Service returns Default Source Id
 */
class DefaultSourceProvider implements DefaultSourceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return 'default';
    }
}
