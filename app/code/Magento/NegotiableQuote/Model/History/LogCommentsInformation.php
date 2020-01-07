<?php

namespace Magento\NegotiableQuote\Model\History;

use Magento\NegotiableQuote\Api\Data\HistoryInterface;

/**
 * Prepares comments information for negotiable quote history log.
 */
class LogCommentsInformation
{
    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface
     */
    private $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Status\LabelProviderInterface
     */
    private $labelProvider;

    /**
     * @param \Magento\NegotiableQuote\Model\CommentManagementInterface $commentManagement
     * @param \Magento\NegotiableQuote\Model\CommentRepositoryInterface $commentRepository
     * @param \Magento\NegotiableQuote\Model\Status\LabelProviderInterface $labelProvider
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\CommentManagementInterface $commentManagement,
        \Magento\NegotiableQuote\Model\CommentRepositoryInterface $commentRepository,
        \Magento\NegotiableQuote\Model\Status\LabelProviderInterface $labelProvider
    ) {
        $this->commentManagement = $commentManagement;
        $this->commentRepository = $commentRepository;
        $this->labelProvider = $labelProvider;
    }

    /**
     * Get history log author name.
     *
     * @param HistoryInterface $historyLog
     * @param int $quoteId
     * @return string
     */
    public function getLogAuthor(HistoryInterface $historyLog, $quoteId)
    {
        if ($historyLog->getAuthorId() && !$this->isAuthorPurged($historyLog)) {
            $author = $this->commentManagement->getCreatorName(
                $historyLog->getAuthorId(),
                $quoteId,
                $historyLog->getIsSeller()
            );
        } else {
            $author = __('System');
        }

        return $author;
    }

    /**
     * Get list of the comment attachments.
     *
     * @param int $commentId
     * @return \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection
     */
    public function getCommentAttachments($commentId)
    {
        return $this->commentManagement->getCommentAttachments($commentId);
    }

    /**
     * Prepare history log comment text.
     *
     * @param int $commentId
     * @return string
     */
    public function getCommentText($commentId)
    {
        $comment = $this->commentRepository->get($commentId);

        if ($comment && $comment->getComment()) {
            return $comment->getComment();
        }

        return '';
    }

    /**
     * Prepare quote status label.
     *
     * @param string $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        return $this->labelProvider->getLabelByStatus($status);
    }

    /**
     * Is history record author purged.
     *
     * @param HistoryInterface $historyLog
     * @return bool
     */
    private function isAuthorPurged(HistoryInterface $historyLog)
    {
        return $historyLog->getStatus() === HistoryInterface::STATUS_CLOSED
            && !$this->commentManagement->checkCreatorLogExists($historyLog->getAuthorId());
    }
}
