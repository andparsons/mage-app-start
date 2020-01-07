<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend\Json\Json;
use Magento\NegotiableQuote\Model\CommentManagement;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Api\Data\CommentAttachmentInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment as CommentAttachmentResource;

/**
 * Controller for save draft quote.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\NegotiableQuote\Controller\Adminhtml\Quote implements HttpPostActionInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagement
     */
    private $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment
     */
    private $commentAttachmentResource;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterfaceFactory
     */
    private $attachmentInterfaceFactory;

    /**
     * @var \Magento\NegotiableQuote\Controller\FileProcessor
     */
    private $fileProcessor;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param CommentManagement $commentManagement
     * @param CommentAttachmentResource $commentAttachmentResource
     * @param CommentAttachmentInterfaceFactory $attachmentInterfaceFactory
     * @param \Magento\NegotiableQuote\Controller\FileProcessor $fileProcessor
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        CommentManagement $commentManagement,
        CommentAttachmentResource $commentAttachmentResource,
        CommentAttachmentInterfaceFactory $attachmentInterfaceFactory,
        \Magento\NegotiableQuote\Controller\FileProcessor $fileProcessor
    ) {
        parent::__construct(
            $context,
            $logger,
            $quoteRepository,
            $negotiableQuoteManagement
        );
        $this->commentManagement = $commentManagement;
        $this->commentAttachmentResource = $commentAttachmentResource;
        $this->attachmentInterfaceFactory = $attachmentInterfaceFactory;
        $this->fileProcessor = $fileProcessor;
    }

    /**
     * Save draft quote.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        try {
            $quoteData = $this->getPreparedQuoteData();
            $commentData = $this->getPreparedCommentData();
            $this->negotiableQuoteManagement->saveAsDraft($quoteId, $quoteData, $commentData);
            $this->deleteAttachments();
        } catch (NoSuchEntityException $e) {
            $this->addNotFoundError();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__('Exception occurred during quote saving'));
        }

        $returnData = $this->getSuccessData($quoteId);

        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData($returnData);
    }

    /**
     * Delete attachments for deleted comments quote.
     *
     * @return void
     */
    private function deleteAttachments()
    {
        $commentIdsToDelete = $this->getRequest()->getParam('delFiles');
        if (!empty($commentIdsToDelete)) {
            foreach (explode(',', $commentIdsToDelete) as $id) {
                $attachment = $this->attachmentInterfaceFactory->create()->load($id);
                $this->commentAttachmentResource->delete($attachment);
            }
        }
    }

    /**
     * Get prepared quote data from request.
     *
     * @return array
     * @throws \Zend\Json\Exception\RuntimeException
     */
    private function getPreparedQuoteData()
    {
        $quoteData = (array)$this->getRequest()->getParam('quote');
        $updateData = (array)Json::decode(
            $this->getRequest()->getParam('dataSend'),
            Json::TYPE_ARRAY
        );
        $quoteUpdateData = $updateData['quote'] ?? [];
        return array_merge($quoteUpdateData, $quoteData);
    }

    /**
     * Get prepared comment data from request.
     *
     * @return array
     */
    private function getPreparedCommentData()
    {
        $commentData = [
            'message' => $this->getRequest()->getParam('comment'),
            'files' => $this->fileProcessor->getFiles()
        ];
        return $commentData;
    }

    /**
     * Get success data for result.
     *
     * Success data contains success status and message
     * If files were attached success data contains file names and attachment ids as well
     *
     * @param int $quoteId
     * @return array
     */
    private function getSuccessData($quoteId)
    {
        $data =[
            'status' => 'success',
            'messages' => [['type' => 'success', 'text' => __('The changes have been saved.')]]
        ];
        if ($this->commentManagement->hasDraftComment($quoteId)) {
            $data['draftCommentFiles'] = [];
            $comment = $this->commentManagement->getQuoteComments($quoteId, true)->getFirstItem();
            foreach ($this->commentManagement->getCommentAttachments($comment->getEntityId()) as $file) {
                $data['draftCommentFiles'][] = ['name' => $file->getFileName(), 'id' => $file->getAttachmentId()];
            }
        }

        return $data;
    }
}
