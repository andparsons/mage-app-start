<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Framework\Controller\Result\RawFactory;

/**
 * Preparing quote shipping methods information.
 */
class ShippingMethod extends \Magento\NegotiableQuote\Controller\Adminhtml\Quote implements HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
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
     * Get quote shipping methods information.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $quote = $this->getQuote();
        $result = '';
        if ($quote) {
            try {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
                $resultPage->addHandle('quotes_quote_shippingMethod');
                $result = $resultPage->getLayout()->renderElement('content');
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Exception occurred during quote view %1', $e->getMessage()));
            }
        }
        return $this->resultRawFactory->create()->setContents($result);
    }
}
