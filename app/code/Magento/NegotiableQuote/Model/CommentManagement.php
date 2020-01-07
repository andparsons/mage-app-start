<?php

declare(strict_types=1);

namespace Magento\NegotiableQuote\Model;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory as CollectionFactory;
use Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\CollectionFactory as AttachmentCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\Escaper;
use Magento\Authorization\Model\UserContextInterface;
use Magento\NegotiableQuote\Model\Attachment\UploadHandler as AttachmentHandler;

/**
 * Class for managing comments.
 */
class CommentManagement implements CommentManagementInterface
{
    /**#@+
     * Comment attachments folder.
     */
    const ATTACHMENTS_FOLDER = 'negotiable_quotes_attachment';
    /**#@-*/

    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\CollectionFactory
     */
    private $attachmentCollectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerNameGeneration;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\UploadHandlerFactory
     */
    private $uploadHandlerFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider
     */
    private $provider;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @param CommentInterfaceFactory $commentFactory
     * @param CollectionFactory $collectionFactory
     * @param AttachmentCollectionFactory $attachmentCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerNameGenerationInterface $customerNameGeneration
     * @param Escaper $escaper
     * @param Attachment\UploadHandlerFactory $uploadHandlerFactory
     * @param \Magento\NegotiableQuote\Model\Purged\Provider $provider
     * @param UserContextInterface $userContext
     */
    public function __construct(
        CommentInterfaceFactory $commentFactory,
        CollectionFactory $collectionFactory,
        AttachmentCollectionFactory $attachmentCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerNameGenerationInterface $customerNameGeneration,
        Escaper $escaper,
        Attachment\UploadHandlerFactory $uploadHandlerFactory,
        \Magento\NegotiableQuote\Model\Purged\Provider $provider,
        UserContextInterface $userContext
    ) {
        $this->commentFactory = $commentFactory;
        $this->collectionFactory = $collectionFactory;
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->customerNameGeneration = $customerNameGeneration;
        $this->escaper = $escaper;
        $this->uploadHandlerFactory = $uploadHandlerFactory;
        $this->provider = $provider;
        $this->userContext = $userContext;
    }

    /**
     * @inheritdoc
     */
    public function update(
        $quoteId,
        $commentText,
        array $files = [],
        $isDeclined = false,
        $isDraft = false
    ) {
        if (!$quoteId || $this->isCommentDeficient($commentText, $files) && !$this->hasDraftComment($quoteId)) {
            return false;
        }

        /** @var \Magento\NegotiableQuote\Api\Data\CommentInterface $comment */
        $comment = $this->commentFactory->create();

        if ($this->hasDraftComment($quoteId)) {
            $commentId = $this->getQuoteComments($quoteId, true)->getFirstItem()->getEntityId();
            $comment->setEntityId($commentId);
            if ($this->isCommentDeficient($commentText, $files)
                && empty($this->getCommentAttachments($commentId)->getItems())) {
                $comment->delete();
                return true;
            }
        }

        $commentText = $this->escaper->escapeHtml($commentText);
        $comment->setCreatorId($this->userContext->getUserId())
            ->setParentId($quoteId)
            ->setCreatorType($this->userContext->getUserType())
            ->setIsDecline($isDeclined)
            ->setIsDraft($isDraft)
            ->setComment($commentText);
        $comment->save();
        $commentId = $comment->getId();
        /** @var AttachmentHandler $uploadHandler */
        $uploadHandler = $this->uploadHandlerFactory->create(['commentId' => $commentId]);
        foreach ($files as $file) {
            $uploadHandler->process($file);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getQuoteComments($quoteId, $isDraft = false)
    {
        /** @var \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection $commentCollection */
        $commentCollection = $this->collectionFactory->create();
        $commentCollection->addFieldToFilter('parent_id', $quoteId)
            ->addFieldToFilter('is_draft', ['eq' => $isDraft]);
        return $commentCollection;
    }

    /**
     * @inheritdoc
     */
    public function getCommentAttachments($commentId)
    {
        $attachmentCollection = $this->attachmentCollectionFactory->create();
        $attachmentCollection->addFieldToFilter('comment_id', $commentId)
            ->setOrder('file_name', 'ASC');
        return $attachmentCollection;
    }

    /**
     * @inheritdoc
     */
    public function getFilesNamesList(array $filesArray)
    {
        $attachmentFields = [];
        if (!empty($filesArray)) {
            foreach ($filesArray as $key => $file) {
                if (!empty($file['tmp_name']) && ($file['size'] > 0)) {
                    $attachmentFields[] = 'files[' . $key . ']';
                }
            }
        }
        return $attachmentFields;
    }

    /**
     * @inheritdoc
     */
    public function getCreatorName($creatorId, $quoteId, $isSeller)
    {
        if ($isSeller) {
            $creator = $this->provider->getSalesRepresentativeName($quoteId);
        } else {
            try {
                $customer = $this->customerRepository->getById($creatorId);
                $creator = $this->customerNameGeneration->getCustomerName($customer);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $creator = $this->provider->getCustomerName($quoteId);
            }
        }

        return $creator;
    }

    /**
     * @inheritdoc
     */
    public function hasDraftComment($quoteId)
    {
        return $this->getQuoteComments($quoteId, true) !== null
            && $this->getQuoteComments($quoteId, true)->getFirstItem() !== null;
    }

    /**
     * @inheritdoc
     */
    public function checkCreatorLogExists($creatorId)
    {
        $result = true;
        try {
            $customer = $this->customerRepository->getById($creatorId);

            if ($customer->getExtensionAttributes()
                && $customer->getExtensionAttributes()->getCompanyAttributes()
                && $customer->getExtensionAttributes()->getCompanyAttributes()->getStatus()
                !== CompanyCustomerInterface::STATUS_ACTIVE
            ) {
                $result = false;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Check is comment text and files deficient.
     *
     * @param string $commentText
     * @param array $fileNames
     * @return bool
     */
    private function isCommentDeficient($commentText, array $fileNames)
    {
        return !trim($commentText) && empty($fileNames);
    }
}
