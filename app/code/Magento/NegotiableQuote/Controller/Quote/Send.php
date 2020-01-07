<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\NegotiableQuote\Controller\Quote;

/**
 * Controller for send quote from buyer to merchant.
 */
class Send extends Quote implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const NEGOTIABLE_QUOTE_RESOURCE = 'Magento_NegotiableQuote::manage';

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address
     */
    private $negotiableQuoteAddress;

    /**
     * @var \Magento\NegotiableQuote\Controller\FileProcessor
     */
    private $fileProcessor;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress
     * @param \Magento\NegotiableQuote\Controller\FileProcessor $fileProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress,
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
        $this->formKeyValidator = $formKeyValidator;
        $this->negotiableQuoteAddress = $negotiableQuoteAddress;
        $this->fileProcessor = $fileProcessor;
    }

    /**
     * Send quote from buyer to merchant.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');
        $comment = $this->getRequest()->getParam('comment');

        if (empty($quoteId)) {
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/view', ['quote_id' => $quoteId]);
        if (! $this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect;
        }

        try {
            $files = $this->fileProcessor->getFiles();
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote->getCustomerId() === $this->settingsProvider->getCurrentUserId()) {
                $this->negotiableQuoteAddress->updateQuoteShippingAddressDraft($quoteId);
                if ($this->negotiableQuoteManagement->send($quoteId, $comment, $files)) {
                    return $this->getResultPage();
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                __('We can\'t send the quote right now because of an error: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t send the quote right now.'));
        }

        return $resultRedirect;
    }
}
