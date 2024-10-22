<?php
namespace Magento\Quote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Product option interface
 * @api
 * @since 100.0.2
 */
interface ProductOptionInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\ProductOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensionAttributes
    );
}
