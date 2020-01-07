<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Controller\Quote;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\Quote\Currency as QuoteCurrency;

/**
 * Class Quote Recalculate Controller.
 */
class Recalculate extends Quote implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const NEGOTIABLE_QUOTE_RESOURCE = 'Magento_NegotiableQuote::view_quotes_sub';

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider
     */
    private $messageProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address
     */
    private $negotiableQuoteAddress;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Currency
     */
    private $quoteCurrency;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider $messageProvider
     * @param \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress
     * @param QuoteCurrency $quoteCurrency
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider $messageProvider,
        \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress,
        QuoteCurrency $quoteCurrency
    ) {
        parent::__construct(
            $context,
            $quoteHelper,
            $quoteRepository,
            $customerRestriction,
            $negotiableQuoteManagement,
            $settingsProvider
        );
        $this->messageProvider = $messageProvider;
        $this->negotiableQuoteAddress = $negotiableQuoteAddress;
        $this->quoteCurrency = $quoteCurrency;
    }

    /**
     * Recalculate quote action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        try {
            if (!$this->canViewQuote($quoteId)) {
                throw new NoSuchEntityException();
            }
            if ($this->customerRestriction->isOwner()) {
                $this->quoteCurrency->updateQuoteCurrency($quoteId);
            }
            if ($this->customerRestriction->canSubmit()) {
                $this->negotiableQuoteAddress->updateQuoteShippingAddressDraft($quoteId);
                $this->prepareForOpen($quoteId);
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('Requested quote was not found'));
            $resultJson = $this->getResultJson();
            return $resultJson->setJsonData(json_encode([], JSON_NUMERIC_CHECK));
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->getResultPage();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->getResultJson();
        $this->setNotifications($resultPage, $quoteId);

        $layout = $resultPage->addHandle('negotiable_quote_quote_view')->getLayout();
        /** @var \Magento\NegotiableQuote\Block\Quote\Items $itemsBlock */
        $itemsBlock = $layout->getBlock('quote_items');
        $itemsBlock->setIsRecalculated();
        $messagesBlock = $layout->getBlock('quote.message');
        $addressBlock = $layout->getBlock('quote.address');
        $responseData = [
            'quote_items' => $itemsBlock->toHtml(),
            'quote_messages' => $messagesBlock->toHtml(),
            'address' => $addressBlock->toHtml()
        ];

        return $resultJson->setJsonData(json_encode($responseData, JSON_NUMERIC_CHECK));
    }

    /**
     * Prepare quote for opening.
     *
     * @param int $quoteId
     * @return void
     */
    private function prepareForOpen($quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $updatePrice = $negotiableQuote->getNegotiatedPriceValue() === null
            || $negotiableQuote->getStatus() == NegotiableQuoteInterface::STATUS_EXPIRED;
        $quote->getExtensionAttributes()->setShippingAssignments([]);
        $this->negotiableQuoteManagement->recalculateQuote($quoteId, $updatePrice);
    }

    /**
     * Quote can be viewed.
     *
     * @param int $quoteId
     * @return bool
     */
    private function canViewQuote($quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $result = ($this->customerRestriction->isOwner() || $this->customerRestriction->isSubUserContent())
            && (bool)$quote->getExtensionAttributes()
                ->getNegotiableQuote()
                ->getIsRegularQuote();

        return $result;
    }

    /**
     * Set notifications for buyer.
     *
     * @param \Magento\Framework\Controller\ResultInterface $resultPage
     * @param int $quoteId
     * @return void
     */
    private function setNotifications(\Magento\Framework\Controller\ResultInterface $resultPage, $quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $initialStatus = $quote->getExtensionAttributes()->getNegotiableQuote()->getNotifications();
        $notifications = $this->messageProvider->getChangesMessages($quote);
        $currentStatus = $quote->getExtensionAttributes()->getNegotiableQuote()->getNotifications();
        if ($this->customerRestriction->isOwner() && ($initialStatus !== $currentStatus || !empty($notifications))) {
            $this->quoteRepository->save($quote);
        }
        /** @var \Magento\NegotiableQuote\Block\Quote\Message $messageBlock */
        $messageBlock = $resultPage->addHandle('negotiable_quote_quote_view')->getLayout()
            ->getBlock('quote.message');
        foreach ($notifications as $message) {
            $messageBlock->setAdditionalMessage($message);
        }

        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()
            && $this->customerRestriction->canCurrencyUpdate()
        ) {
            $currencyNotification = __(
                'This quote will be charged in %1. The prices are given in %2 as reference only.',
                $quote->getBaseCurrencyCode(),
                $quote->getQuoteCurrencyCode()
            );
            $messageBlock->setAdditionalMessage($currencyNotification);
        }
    }

    /**
     * @inheritdoc
     */
    protected function isAllowed()
    {
        if ($this->customerRestriction->isAllowed('Magento_NegotiableQuote::view_quotes')) {
            if (!$this->customerRestriction->isAllowed('Magento_NegotiableQuote::view_quotes_sub')) {
                $quoteId = $this->getRequest()->getParam('quote_id');
                $this->quoteRepository->get($quoteId);
                return $this->customerRestriction->isOwner();
            }
            return true;
        }
        return false;
    }
}
