<?php
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Shipment Track Creation interface.
 *
 * @api
 * @since 100.1.2
 */
interface ShipmentTrackCreationInterface extends TrackInterface, ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface|null
     * @since 100.1.2
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.1.2
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentTrackCreationExtensionInterface $extensionAttributes
    );
}
