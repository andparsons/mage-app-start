<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CommentInterface
 * @api
 * @since 100.0.0
 */
interface CommentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const CREATOR_TYPE = 'creator_type';
    const IS_DECLINE = 'is_decline';
    const IS_DRAFT = 'is_draft';
    const CREATOR_ID = 'creator_id';
    const COMMENT = 'comment';
    const CREATED_AT = 'created_at';
    const ATTACHMENTS = 'attachments';
    /**#@-*/

    /**
     * Get comment ID.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set comment ID.
     *
     * @param int $id
     * @return $this
     */
    public function setEntityId($id);

    /**
     * Get negotiable quote ID, that this comment belongs to.
     *
     * @return int
     */
    public function getParentId();

    /**
     * Set negotiable quote ID, that this comment belongs to.
     *
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * Returns the comment creator type.
     *
     * @return int
     */
    public function getCreatorType();

    /**
     * Set comment creator type.
     *
     * @param int $creatorType
     * @return $this
     */
    public function setCreatorType($creatorType);

    /**
     * Get is quote was declined by seller.
     *
     * @return int
     */
    public function getIsDecline();

    /**
     * Set that quote was declined by seller.
     *
     * @param int $flag
     * @return $this
     */
    public function setIsDecline($flag);

    /**
     * Get is quote draft flag.
     *
     * @return int
     */
    public function getIsDraft();

    /**
     * Set is quote draft flag.
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsDraft($flag);

    /**
     * Get comment creator ID.
     *
     * @return int
     */
    public function getCreatorId();

    /**
     * Set comment creator ID.
     *
     * @param int $creatorId
     * @return $this
     */
    public function setCreatorId($creatorId);

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment();

    /**
     * Set comment.
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Get comment created at.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set comment created at.
     *
     * @param int $timestamp
     * @return $this
     */
    public function setCreatedAt($timestamp);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\CommentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\CommentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\CommentExtensionInterface $extensionAttributes
    );

    /**
     * Set attachments for comment.
     *
     * @param \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface[] $attachments
     * @return $this
     */
    public function setAttachments(array $attachments);

    /**
     * Retrieve existing attachments.
     *
     * @return \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface[]
     */
    public function getAttachments();
}
