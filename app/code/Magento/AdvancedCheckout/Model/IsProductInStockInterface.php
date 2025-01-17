<?php
declare(strict_types=1);

namespace Magento\AdvancedCheckout\Model;

/**
 * Service which detects whether Product identified by id is in stock for a given Website id
 * It is introduced to allow to preference and pluginize the old protected method.
 */
interface IsProductInStockInterface
{
    /**
     * Get is product out of stock for given Product id in a given Website id
     *
     * @param int $productId
     * @param int $websiteId
     * @return bool
     */
    public function execute(int $productId, int $websiteId): bool;
}
