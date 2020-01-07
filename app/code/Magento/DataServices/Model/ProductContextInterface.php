<?php
declare(strict_types=1);

namespace Magento\DataServices\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface for Product Context model
 *
 * @api
 */
interface ProductContextInterface
{
    /**
     * Return product context for data services events
     *
     * @param Product $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getContextData(Product $product): array;
}
