<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\AttachmentContentInterface;

/**
 * Data object for attachment files.
 */
class AttachmentContent extends \Magento\Framework\Model\AbstractExtensibleModel implements AttachmentContentInterface
{
    /**
     * @inheritdoc
     */
    public function getBase64EncodedData()
    {
        return $this->getData(self::BASE64_ENCODED_DATA);
    }

    /**
     * @inheritdoc
     */
    public function setBase64EncodedData($data)
    {
        return $this->setData(self::BASE64_ENCODED_DATA, $data);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($mimeType)
    {
        return $this->setData(self::TYPE, $mimeType);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\AttachmentContentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
