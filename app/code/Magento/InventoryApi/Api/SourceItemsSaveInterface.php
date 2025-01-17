<?php
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Service method for source items save multiple
 * Performance efficient API
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemsSaveInterface
{
    /**
     * Save Multiple Source item data
     *
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $sourceItems): void;
}
