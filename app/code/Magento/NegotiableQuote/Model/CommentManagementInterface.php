<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection as AttachmentCollection;
use Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection as CommentCollection;

/**
 * Interface for managing comments.
 * @api
 * @since 100.0.0
 */
interface CommentManagementInterface
{
    /**
     * Update comment for quote.
     *
     * @param int $quoteId
     * @param string $commentText
     * @param \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface[] $files [optional]
     * @param bool $isDeclined [optional]
     * @param bool $isDraft [optional]
     * @return bool
     */
    public function update(
        $quoteId,
        $commentText,
        array $files = [],
        $isDeclined = false,
        $isDraft = false
    );

    /**
     * Get list of comments for quote.
     *
     * @param int $quoteId
     * @param bool $isDraft [optional]
     * @return CommentCollection
     */
    public function getQuoteComments($quoteId, $isDraft = false);

    /**
     * Returns collection of attachment for comment.
     *
     * @param int $commentId
     * @return AttachmentCollection
     */
    public function getCommentAttachments($commentId);

    /**
     * Get list of all attachments.
     *
     * @param array $filesArray
     * @return array|null
     */
    public function getFilesNamesList(array $filesArray);

    /**
     * Get author name for comment.
     *
     * @param int $creatorId
     * @param int $quoteId
     * @param bool $isSeller
     * @return string
     */
    public function getCreatorName($creatorId, $quoteId, $isSeller);

    /**
     * Check if quote has draft comments.
     *
     * @param int $quoteId
     * @return bool
     */
    public function hasDraftComment($quoteId);

    /**
     * Check if creator company unapproved company log exists.
     *
     * @param int $creatorId
     * @return bool
     */
    public function checkCreatorLogExists($creatorId);
}
