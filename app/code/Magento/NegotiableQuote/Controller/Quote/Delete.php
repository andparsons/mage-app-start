<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\NegotiableQuote\Controller\Quote;
use Magento\Framework\App\Action\Context;
use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;

/**
 * Class Delete
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Delete extends Quote implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const NEGOTIABLE_QUOTE_RESOURCE = 'Magento_NegotiableQuote::manage';

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface
     */
    private $quoteGrid;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * Delete constructor
     *
     * @param Context $context
     * @param QuoteHelper $quoteHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param RestrictionInterface $customerRestriction
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param \Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface $quoteGrid
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        QuoteHelper $quoteHelper,
        CartRepositoryInterface $quoteRepository,
        RestrictionInterface $customerRestriction,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        \Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface $quoteGrid,
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
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteGrid = $quoteGrid;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Delete negotiable quote
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        if (! $this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect;
        }

        $quoteId = $this->getRequest()->getParam('quote_id');

        if ($quoteId) {
            try {
                $quote = $this->quoteRepository->get($quoteId);
                $negotiableQuote = $this->negotiableQuoteRepository->getById($quoteId);
                if ($this->customerRestriction->canDelete()
                    && $quote->getCustomerId() === $this->settingsProvider->getCurrentUserId()) {
                    $this->negotiableQuoteRepository->delete($negotiableQuote);
                    $this->quoteGrid->remove($quote);
                    $this->messageManager->addSuccessMessage(__('You have deleted the quote.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t delete the quote right now.'));
            }
        }
        return $resultRedirect;
    }
}
