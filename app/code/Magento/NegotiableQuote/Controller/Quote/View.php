<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Controller\Quote;

/**
 * Negotiable Quote View Controller.
 */
class View extends Quote implements HttpGetActionInterface
{

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ViewAccessInterface
     */
    private $viewAccess;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\NegotiableQuote\Model\Quote\ViewAccessInterface $viewAccess
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\NegotiableQuote\Model\Quote\ViewAccessInterface $viewAccess
    ) {
        parent::__construct(
            $context,
            $quoteHelper,
            $quoteRepository,
            $customerRestriction,
            $negotiableQuoteManagement,
            $settingsProvider
        );

        $this->viewAccess = $viewAccess;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $quote = $this->quoteHelper->resolveCurrentQuote();
        if (!$quote) {
            return $this->processException(__('Requested quote was not found'));
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->getResultPage();
        $resultPage->getConfig()->getTitle()->set(__('Quote'));
        $this->setNavigationBlockActive($resultPage);

        return $resultPage;
    }

    /**
     * Process exception.
     *
     * @param string $message
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function processException($message)
    {
        $this->messageManager->addErrorMessage($message);
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * Set navigation block active.
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return void
     */
    private function setNavigationBlockActive(\Magento\Framework\View\Result\Page $resultPage)
    {
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('negotiable_quote/quote');
        }
    }

    /**
     * @inheritDoc
     */
    protected function isAllowed()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');
        if ($quoteId) {
            try {
                $quote = $this->negotiableQuoteManagement->getNegotiableQuote($quoteId);
            } catch (NoSuchEntityException $exception) {
                return true;
            }

            return $this->viewAccess->canViewQuote($quote);
        }

        return true;
    }
}
