<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface;

/**
 * DTO for CommentAttachment entity.
 */
class CommentAttachment extends AbstractExtensibleModel implements CommentAttachmentInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment::class);
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function getAttachmentId()
    {
        return $this->getData(CommentAttachmentInterface::ATTACHMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAttachmentId($id)
    {
        return $this->setData(CommentAttachmentInterface::ATTACHMENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getCommentId()
    {
        return $this->getData(CommentAttachmentInterface::COMMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCommentId($id)
    {
        return $this->setData(CommentAttachmentInterface::COMMENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getFileName()
    {
        return $this->getData(CommentAttachmentInterface::FILE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setFileName($name)
    {
        return $this->setData(CommentAttachmentInterface::FILE_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getFilePath()
    {
        return $this->getData(CommentAttachmentInterface::FILE_PATH);
    }

    /**
     * @inheritdoc
     */
    public function setFilePath($path)
    {
        return $this->setData(CommentAttachmentInterface::FILE_PATH, $path);
    }

    /**
     * @inheritdoc
     */
    public function getFileType()
    {
        return $this->getData(CommentAttachmentInterface::FILE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setFileType($type)
    {
        return $this->setData(CommentAttachmentInterface::FILE_TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\CommentAttachmentExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
