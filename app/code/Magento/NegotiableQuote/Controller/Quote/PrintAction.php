<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Controller\Quote;

/**
 * Class Quote Print Controller
 */
class PrintAction extends Quote implements HttpGetActionInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address
     */
    private $negotiableQuoteAddress;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ViewAccessInterface
     */
    private $viewAccess;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress
     * @param \Magento\NegotiableQuote\Model\Quote\ViewAccessInterface $viewAccess
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress,
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
        $this->negotiableQuoteAddress = $negotiableQuoteAddress;
        $this->viewAccess = $viewAccess;
    }

    /**
     * Print customer quotes actions
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $quote = $this->quoteHelper->resolveCurrentQuote();
        if (!$quote) {
            $this->messageManager->addError(
                __('Requested quote was not found')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        $this->negotiableQuoteAddress
            ->updateQuoteShippingAddressDraft($quote->getId());
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->getResultPage();
        $resultPage->getConfig()->getTitle()->set(__('Quote'));

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('negotiable_quote/quote');
        }

        return $resultPage;
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
