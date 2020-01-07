<?php

declare(strict_types=1);

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\NegotiableQuote\Model\Attachment\DownloadProvider;
use Magento\NegotiableQuote\Model\Attachment\DownloadProviderFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Class Download
 */
class Download extends Action implements HttpGetActionInterface
{
    /**
     * Download provider factory
     *
     * @var DownloadProviderFactory
     */
    private $downloadProviderFactory;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DownloadProviderFactory $downloadProviderFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        DownloadProviderFactory $downloadProviderFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->downloadProviderFactory = $downloadProviderFactory;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute(): void
    {
        $attachmentId = $this->getRequest()->getParam('attachmentId');
        /** @var DownloadProvider $downloadProvider */
        $downloadProvider = $this->downloadProviderFactory->create(['attachmentId' => $attachmentId]);

        try {
            $downloadProvider->getAttachmentContents();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new NotFoundException(__('Attachment not found.'));
        }
    }
}
