<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class ItemUpdate
 */
class ItemUpdate extends \Magento\NegotiableQuote\Controller\Quote implements HttpPostActionInterface
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
     * ItemUpdate constructor
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
     * Update items in the negotiable quote
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/view', ['quote_id' => $quoteId]);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect;
        }

        if ($quoteId) {
            try {
                $quote = $this->quoteRepository->get($quoteId);
                if ($this->customerRestriction->canSubmit()
                    && $quote->getCustomerId() === $this->settingsProvider->getCurrentUserId()
                ) {
                    $cartData = $this->getRequest()->getParam('cart');
                    $this->negotiableQuoteManagement->updateQuoteItems($quoteId, $cartData);
                    $this->negotiableQuoteManagement->updateProcessingByCustomerQuoteStatus($quoteId);
                    $this->messageManager->addSuccessMessage(__('You have updated the items in the quote.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t update the items right now.'));
            }
        }
        return $resultRedirect;
    }
}
