<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class Close
 */
class Close extends \Magento\NegotiableQuote\Controller\Quote implements HttpPostActionInterface
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
     * Close constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
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
    }

    /**
     * Close quote
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/view', ['quote_id' => $quoteId]);
        if (! $this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect;
        }

        try {
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote->getCustomerId() === $this->settingsProvider->getCurrentUserId()) {
                if ($this->negotiableQuoteManagement->close($quoteId)) {
                    $this->messageManager->addSuccess(__('You closed the quote.'));
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                __('We can\'t close the quote right now because of an error: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t close the quote right now.'));
        }

        return $resultRedirect;
    }
}
