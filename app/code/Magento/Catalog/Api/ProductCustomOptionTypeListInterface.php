<?php

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductCustomOptionTypeListInterface
{
    /**
     * Get custom option types
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterface[]
     */
    public function getItems();
}
