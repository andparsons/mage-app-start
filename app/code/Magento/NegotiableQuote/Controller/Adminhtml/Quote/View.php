<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;

/**
 * Class View.
 */
class View extends \Magento\NegotiableQuote\Controller\Adminhtml\Quote implements HttpGetActionInterface
{
    /**
     * Array of actions which can be processed without secret key validation.
     *
     * @var array
     */
    protected $_publicActions = ['view'];

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider
     */
    private $messageProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider $messageProvider
     * @param \Magento\NegotiableQuote\Model\Cart $cart
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider $messageProvider,
        \Magento\NegotiableQuote\Model\Cart $cart,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
    ) {
        parent::__construct(
            $context,
            $logger,
            $quoteRepository,
            $negotiableQuoteManagement
        );
        $this->messageProvider = $messageProvider;
        $this->cart = $cart;
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
    }

    /**
     * View quote details.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        try {
            $quote = $this->quoteRepository->get($quoteId, ['*']);
            $this->cart->removeAllFailed();
            $this->setNotifications($quote);
        } catch (NoSuchEntityException $e) {
            $this->addNotFoundError();
            return $this->redirectOnIndexPage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('An error occurred on the server. %1', $e->getMessage()));
            return $this->redirectOnIndexPage();
        }
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Quote #%1', $quoteId));
        return $resultPage;
    }

    /**
     * Redirect on index page
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function redirectOnIndexPage()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * Set notifications for merchant.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    private function setNotifications(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $notifications = $this->messageProvider->getChangesMessages($quote);

        foreach ($notifications as $message) {
            if ($message) {
                $this->messageManager->addWarningMessage($message);
            }
        }

        if ($this->negotiableQuoteHelper->isLockMessageDisplayed()) {
            $this->messageManager->addWarningMessage(
                __('This quote is currently locked for editing. It will become available once released by the buyer.')
            );
        }
    }
}
