<?php
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Inventory\Model\SourceItem\Command\Handler\SourceItemsSaveHandler;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

/**
 * @inheritdoc
 */
class SourceItemsSaveWithoutLegacySynchronization implements SourceItemsSaveInterface
{
    /**
     * @var SourceItemsSaveHandler
     */
    private $sourceItemsSaveHandler;

    /**
     * @param SourceItemsSaveHandler $sourceItemsSaveHandler
     */
    public function __construct(SourceItemsSaveHandler $sourceItemsSaveHandler)
    {
        $this->sourceItemsSaveHandler = $sourceItemsSaveHandler;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceItems): void
    {
        $this->sourceItemsSaveHandler->execute($sourceItems);
    }
}
