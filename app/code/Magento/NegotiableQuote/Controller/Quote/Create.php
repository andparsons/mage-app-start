<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Controller\Quote;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;

/**
 * Controller for create negotiable quote.
 */
class Create extends Quote implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const NEGOTIABLE_QUOTE_RESOURCE = 'Magento_NegotiableQuote::manage';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\NegotiableQuote\Controller\FileProcessor
     */
    private $fileProcessor;

    /**
     * @param Context $context
     * @param QuoteHelper $quoteHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param RestrictionInterface $customerRestriction
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\NegotiableQuote\Controller\FileProcessor $fileProcessor
     */
    public function __construct(
        Context $context,
        QuoteHelper $quoteHelper,
        CartRepositoryInterface $quoteRepository,
        RestrictionInterface $customerRestriction,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\NegotiableQuote\Controller\FileProcessor $fileProcessor
    ) {
        parent::__construct(
            $context,
            $quoteHelper,
            $quoteRepository,
            $customerRestriction,
            $negotiableQuoteManagement,
            $settingsProvider
        );
        $this->checkoutSession = $checkoutSession;
        $this->fileProcessor = $fileProcessor;
    }

    /**
     * Create negotiable quote and redirect to success page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $quoteName = $this->getRequest()->getParam('quote-name');
        $commentText = $this->getRequest()->getParam('quote-message');
        $result = $this->settingsProvider->retrieveJsonError(__('An error occurred while creating quote.'));

        $quoteId = $this->getRequest()->getParam('quote_id');
        if ($quoteId) {
            try {
                $files = $this->fileProcessor->getFiles();
                $quote = $this->quoteRepository->get($quoteId, ['*']);
                $this->removeAddresses($quote);
                $url = $this->_url->getUrl('negotiable_quote/quote');
                $this->negotiableQuoteManagement->create($quoteId, $quoteName, $commentText, $files);
                $data = [
                    'quote_id' => $quoteId,
                    'url' => $url
                ];
                $result = $this->settingsProvider->retrieveJsonSuccess(
                    $data,
                    __('Quote with ID %1 was successfully created.', $quoteId)
                );
                $this->checkoutSession->clearQuote();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('The quote could not be created. Please try again later.')
                );
            }
        }

        return $result;
    }

    /**
     * Remove address from quote.
     *
     * @param CartInterface $quote
     * @return $this
     */
    private function removeAddresses(CartInterface $quote)
    {
        if ($quote->getBillingAddress()) {
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->getBillingAddress();
        }
        if ($quote->getShippingAddress()) {
            $quote->removeAddress($quote->getShippingAddress()->getId());
            $quote->getShippingAddress();
        }
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getShippingAssignments()) {
            $quote->getExtensionAttributes()->setShippingAssignments(null);
        }
        return $this;
    }
}
