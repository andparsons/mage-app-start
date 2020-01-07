<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Attachment files content interface.
 *
 * @api
 * @since 100.0.0
 */
interface AttachmentContentInterface extends ExtensibleDataInterface
{
    const BASE64_ENCODED_DATA = 'base64_encoded_data';
    const TYPE = 'type';
    const NAME = 'name';

    /**
     * Retrieve media data (base64 encoded content).
     *
     * @return string
     */
    public function getBase64EncodedData();

    /**
     * Set media data (base64 encoded content).
     *
     * @param string $data
     * @return $this
     */
    public function setBase64EncodedData($data);

    /**
     * Retrieve MIME type.
     *
     * @return string
     */
    public function getType();

    /**
     * Set MIME type.
     *
     * @param string $mimeType
     * @return $this
     */
    public function setType($mimeType);

    /**
     * Retrieve file name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set file name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\AttachmentContentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\AttachmentContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\AttachmentContentExtensionInterface $extensionAttributes
    );
}
