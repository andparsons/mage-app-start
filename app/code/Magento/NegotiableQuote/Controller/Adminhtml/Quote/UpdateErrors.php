<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Controller\Adminhtml\Quote as QuoteController;

/**
 * Updating quote errors information.
 */
class UpdateErrors extends QuoteController implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        RawFactory $resultRawFactory
    ) {
        parent::__construct(
            $context,
            $logger,
            $quoteRepository,
            $negotiableQuoteManagement
        );
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Updating quote errors information.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->addHandle('sales_order_create_load_block_json');
        $resultPage->addHandle('quotes_quote_update_load_block_errors');
        $result = $resultPage->getLayout()->renderElement('content');
        return $this->resultRawFactory->create()->setContents($result);
    }
}
