<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\NegotiableQuote\Api\Data\CommentInterface;

/**
 * Data transfer class for Negotiable Quote Comment.
 */
class Comment extends AbstractExtensibleModel implements CommentInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\NegotiableQuote\Model\ResourceModel\Comment::class);
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->getData(CommentInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($id)
    {
        return $this->setData(CommentInterface::ENTITY_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getParentId()
    {
        return $this->getData(CommentInterface::PARENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setParentId($id)
    {
        return $this->setData(CommentInterface::PARENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getCreatorType()
    {
        return $this->getData(CommentInterface::CREATOR_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setCreatorType($creatorType)
    {
        return $this->setData(CommentInterface::CREATOR_TYPE, $creatorType);
    }

    /**
     * @inheritdoc
     */
    public function getIsDecline()
    {
        return $this->getData(CommentInterface::IS_DECLINE);
    }

    /**
     * @inheritdoc
     */
    public function setIsDecline($flag)
    {
        return $this->setData(CommentInterface::IS_DECLINE, $flag);
    }

    /**
     * @inheritdoc
     */
    public function getIsDraft()
    {
        return $this->getData(CommentInterface::IS_DRAFT);
    }

    /**
     * @inheritdoc
     */
    public function setIsDraft($flag)
    {
        return $this->setData(CommentInterface::IS_DRAFT, $flag);
    }

    /**
     * @inheritdoc
     */
    public function getCreatorId()
    {
        return $this->getData(CommentInterface::CREATOR_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCreatorId($creatorId)
    {
        return $this->setData(CommentInterface::CREATOR_ID, $creatorId);
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->getData(CommentInterface::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        return $this->setData(CommentInterface::COMMENT, $comment);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(CommentInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($timestamp)
    {
        return $this->setData(CommentInterface::CREATED_AT, $timestamp);
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
        \Magento\NegotiableQuote\Api\Data\CommentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritdoc
     */
    public function getAttachments()
    {
        return $this->getData(CommentInterface::ATTACHMENTS);
    }

    /**
     * @inheritdoc
     */
    public function setAttachments(array $attachments)
    {
        return $this->setData(CommentInterface::ATTACHMENTS, $attachments);
    }
}
