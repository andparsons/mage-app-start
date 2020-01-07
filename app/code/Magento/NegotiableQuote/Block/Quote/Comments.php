<?php

namespace Magento\NegotiableQuote\Block\Quote;

use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\NegotiableQuote\Helper\Quote as NegotiableQuoteHelper;
use Magento\NegotiableQuote\Api\Data\CommentInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\NegotiableQuote\Model\Config as NegotiableQuoteConfig;

/**
 * Class with Quote Comments operations.
 *
 * @api
 * @since 100.0.0
 */
class Comments extends AbstractQuote
{
    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface
     */
    private $commentManagement;

    /**
     * @var \Magento\Framework\File\Size
     */
    private $fileSize;

    /**
     * @var NegotiableQuoteConfig
     */
    private $negotiableQuoteConfig;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\NegotiableQuote\Model\Creator
     */
    private $creator;

    /**
     * @param TemplateContext $context
     * @param PostHelper $postDataHelper
     * @param NegotiableQuoteHelper $negotiableQuoteHelper
     * @param \Magento\NegotiableQuote\Model\CommentManagementInterface $commentManagement
     * @param \Magento\Framework\File\Size $fileSize
     * @param NegotiableQuoteConfig $negotiableQuoteConfig
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\NegotiableQuote\Model\Creator $creator
     * @param array $data [optional]
     */
    public function __construct(
        TemplateContext $context,
        PostHelper $postDataHelper,
        NegotiableQuoteHelper $negotiableQuoteHelper,
        \Magento\NegotiableQuote\Model\CommentManagementInterface $commentManagement,
        \Magento\Framework\File\Size $fileSize,
        NegotiableQuoteConfig $negotiableQuoteConfig,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\NegotiableQuote\Model\Creator $creator,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $negotiableQuoteHelper, $data);
        $this->commentManagement = $commentManagement;
        $this->negotiableQuoteConfig = $negotiableQuoteConfig;
        $this->fileSize = $fileSize;
        $this->authorization = $authorization;
        $this->creator = $creator;
    }

    /**
     * Get quote helper.
     *
     * @return NegotiableQuoteHelper
     */
    public function getQuoteHelper()
    {
        return $this->negotiableQuoteHelper;
    }

    /**
     * Get quote comments.
     *
     * @return array
     */
    public function getQuoteComments()
    {
        if ($this->getQuote()) {
            $quoteId = $this->getQuote()->getEntityId();
            return $this->commentManagement->getQuoteComments($quoteId);
        }
        return [];
    }

    /**
     * Get quote draft comment.
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function getDraftComment()
    {
        $hasDraftComments = $this->getQuote() !== null
            && $this->commentManagement->hasDraftComment($this->getQuote()->getEntityId());
        return $hasDraftComments
            ? $this->commentManagement->getQuoteComments($this->getQuote()->getEntityId(), true)
                ->getFirstItem()
            : null;
    }

    /**
     * Returns collection of attachments.
     *
     * @param int $commentId
     * @return array|\Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection
     */
    public function getCommentAttachments($commentId)
    {
        if ($commentId) {
            return $this->commentManagement->getCommentAttachments($commentId);
        }
        return [];
    }

    /**
     * Returns attachment URL.
     *
     * @param int $attachmentId
     * @return string
     */
    public function getAttachmentUrl($attachmentId)
    {
        return $this->getUrl('negotiable_quote/quote/download', ['attachmentId' => $attachmentId]);
    }

    /**
     * Returns creator name.
     *
     * @param CommentInterface $comment
     * @return string
     */
    public function getCommentCreator(CommentInterface $comment)
    {
        if ($comment->getCreatorId()) {
            $author = $this->creator->retrieveCreatorName(
                $comment->getCreatorType(),
                $comment->getCreatorId(),
                $this->getQuote()->getId()
            );

            return '(' . $author . ')';
        }

        return '';
    }

    /**
     * Get maximum allowed file size in bytes.
     *
     * @return float
     */
    public function getMaxFileSize()
    {
        return $this->fileSize->convertSizeToInteger($this->getMaxFileSizeMb() . 'M');
    }

    /**
     * Get maximum allowed file size in Mb.
     *
     * @return float
     */
    public function getMaxFileSizeMb()
    {
        $configSize = $this->negotiableQuoteConfig->getMaxFileSize();
        $phpLimit = $this->fileSize->getMaxFileSizeInMb();
        if ($configSize) {
            return min($configSize, $phpLimit);
        }
        return $phpLimit;
    }

    /**
     * Get allowed file extensions.
     *
     * @return string
     */
    public function getAllowedExtensions()
    {
        return $this->negotiableQuoteConfig->getAllowedExtensions();
    }

    /**
     * Get delete attachment url.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/deleteAttachment');
    }

    /**
     * Check if negotiable quote management is allowed.
     *
     * @return bool
     */
    public function isAllowedManage()
    {
        return $this->authorization->isAllowed('Magento_NegotiableQuote::manage');
    }
}
