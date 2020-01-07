<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Controller for send quote from merchant to buyer.
 */
class Send extends \Magento\NegotiableQuote\Controller\Adminhtml\Quote implements HttpPostActionInterface
{
    /**
     * @var \Magento\NegotiableQuote\Controller\FileProcessor
     */
    private $fileProcessor;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Controller\FileProcessor $fileProcessor
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Controller\FileProcessor $fileProcessor,
        SerializerInterface $serializer
    ) {
        parent::__construct($context, $logger, $quoteRepository, $negotiableQuoteManagement);
        $this->fileProcessor = $fileProcessor;
        $this->serializer = $serializer;
    }

    /**
     * Send quote from merchant to buyer.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $commentText = $this->getRequest()->getParam('comment');
        $files = $this->fileProcessor->getFiles();
        try {
            $data = $this->prepareQuoteData();
            $this->negotiableQuoteManagement->saveAsDraft($quoteId, $data);
            $this->negotiableQuoteManagement->adminSend($quoteId, $commentText, $files);
            $this->messageManager->addSuccessMessage(__('The quote has been sent to the buyer.'));
        } catch (NoSuchEntityException $e) {
            $this->addNotFoundError();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Exception occurred during quote sending'));
        }
        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData(
                [
                    'status' => 'success'
                ]
            );
    }

    /**
     * Prepare quote data array from request.
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function prepareQuoteData()
    {
        $quoteData = (array)$this->getRequest()->getParam('quote');
        $updateData = (array)$this->serializer->unserialize(
            $this->getRequest()->getParam('dataSend')
        );
        $quoteUpdateData = $updateData['quote'] ?? [];
        return array_merge($quoteUpdateData, $quoteData);
    }
}
