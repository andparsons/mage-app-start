<?php

namespace Magento\NegotiableQuote\Model\Attachment\DownloadPermission;

/**
 * Class AllowCustomer
 */
class AllowCustomer implements AllowInterface
{
    /**
     * User session
     *
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * Comment attachment factory
     *
     * @var \Magento\NegotiableQuote\Model\CommentAttachmentFactory
     */
    private $commentAttachmentFactory;

    /**
     * Quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Comment repository
     *
     * @var \Magento\NegotiableQuote\Api\CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * Company structure
     *
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structure;

    /**
     * AllowCustomer constructor
     *
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\NegotiableQuote\Model\CommentAttachmentFactory $commentAttachmentFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\CommentRepositoryInterface $commentRepository
     * @param \Magento\Company\Model\Company\Structure $structure
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\NegotiableQuote\Model\CommentAttachmentFactory $commentAttachmentFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\CommentRepositoryInterface $commentRepository,
        \Magento\Company\Model\Company\Structure $structure
    ) {
        $this->userContext = $userContext;
        $this->commentAttachmentFactory = $commentAttachmentFactory;
        $this->quoteRepository = $quoteRepository;
        $this->commentRepository = $commentRepository;
        $this->structure = $structure;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($attachmentId)
    {
        /** @var \Magento\NegotiableQuote\Model\CommentAttachment $attachment */
        $attachment = $this->commentAttachmentFactory->create()->load($attachmentId);
        $comment = $this->commentRepository->get($attachment->getCommentId());
        $quote = $this->quoteRepository->get($comment->getParentId(), ['*']);
        $allowedIds = $this->structure->getAllowedChildrenIds($this->userContext->getUserId());
        $allowedIds[] =  $this->userContext->getUserId();
        return $quote->getCustomer()->getId() === $this->userContext->getUserId()
        || in_array($quote->getCustomer()->getId(), $allowedIds);
    }
}
