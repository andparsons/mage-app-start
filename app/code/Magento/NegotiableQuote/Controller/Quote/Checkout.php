<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Controller\Quote;
use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\NegotiableQuote\Model\CheckoutQuoteValidator;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Model\SettingsProvider;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * A proxy class to validate quote items stock status before proceed to checkout
 */
class Checkout extends Quote implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const NEGOTIABLE_QUOTE_RESOURCE = 'Magento_NegotiableQuote::checkout';

    /**
     * @var CheckoutQuoteValidator
     */
    private $checkoutQuoteValidator;

    /**
     * @var NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * Constructor
     *
     * @param Context $context
     * @param QuoteHelper $quoteHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param RestrictionInterface $customerRestriction
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param SettingsProvider $settingsProvider
     * @param CheckoutQuoteValidator $checkoutQuoteValidator
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     */
    public function __construct(
        Context $context,
        QuoteHelper $quoteHelper,
        CartRepositoryInterface $quoteRepository,
        RestrictionInterface $customerRestriction,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        SettingsProvider $settingsProvider,
        CheckoutQuoteValidator $checkoutQuoteValidator,
        NegotiableQuoteItemManagementInterface $quoteItemManagement
    ) {
        parent::__construct(
            $context,
            $quoteHelper,
            $quoteRepository,
            $customerRestriction,
            $negotiableQuoteManagement,
            $settingsProvider
        );
        $this->checkoutQuoteValidator = $checkoutQuoteValidator;
        $this->quoteItemManagement = $quoteItemManagement;
    }

    /**
     * View customer quotes actions
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        if (!$quoteId) {
            $quoteId = $this->getRequest()->getParam('negotiableQuoteId');
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/view', ['quote_id' => $quoteId]);

        $quote = $this->quoteRepository->get($quoteId);
        $quote->getExtensionAttributes()->setShippingAssignments(null);
        if ($this->customerRestriction->canSubmit()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getNegotiatedPriceValue() === null
        ) {
            $this->quoteItemManagement->recalculateOriginalPriceTax($quoteId, true, true);
        }

        $invalidQtyItems = $this->checkoutQuoteValidator->countInvalidQtyItems($quote);
        if ($invalidQtyItems > 0) {
            $message = __(
                '%1 products require your attention. Please contact the Seller if you have any questions.',
                $invalidQtyItems
            );
            $this->messageManager->addError($message);

            return $resultRedirect;
        }

        $resultRedirect->setPath('checkout/index/index', [
            'negotiableQuoteId' => $quoteId
        ]);

        return $resultRedirect;
    }
}
