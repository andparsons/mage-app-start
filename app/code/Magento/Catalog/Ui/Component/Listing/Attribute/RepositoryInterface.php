<?php
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

/**
 * @api
 * @since 100.0.2
 */
interface RepositoryInterface
{
    /**
     * Get attributes
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function getList();
}
