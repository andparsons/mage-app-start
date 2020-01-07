<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Model\QuoteUpdater;
use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\NegotiableQuote\Model\Quote\Currency as QuoteCurrency;

/**
 * Load updated quote information during quote opening process.
 */
class UpdateOnOpen extends \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Update implements HttpPostActionInterface
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param QuoteUpdater $quoteUpdater
     * @param \Magento\AdvancedCheckout\Model\CartFactory $cartFactory
     * @param \Magento\NegotiableQuote\Model\QuoteUpdatesInfo $quoteUpdatesInfo
     * @param QuoteCurrency $quoteCurrency
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        QuoteUpdater $quoteUpdater,
        \Magento\AdvancedCheckout\Model\CartFactory $cartFactory,
        \Magento\NegotiableQuote\Model\QuoteUpdatesInfo $quoteUpdatesInfo,
        QuoteCurrency $quoteCurrency,
        QuoteHelper $quoteHelper
    ) {
        parent::__construct(
            $context,
            $logger,
            $quoteRepository,
            $negotiableQuoteManagement,
            $quoteUpdater,
            $cartFactory,
            $quoteUpdatesInfo,
            $quoteCurrency
        );
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * Recalculate action, load updated quote data.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = [];
        $quoteId = $this->getRequest()->getParam('quote_id');
        $this->quoteData = $this->prepareQuoteData();
        try {
            $quote = $this->quoteRepository->get($quoteId, ['*']);
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $oldIsPriceChanged = $negotiableQuote->getIsCustomerPriceChanged();
            $this->quoteCurrency->updateQuoteCurrency($quoteId);
            $this->negotiableQuoteManagement->openByMerchant($quoteId);
            if ($negotiableQuote->getNegotiatedPriceValue() === null) {
                $negotiableQuote->setIsCustomerPriceChanged($oldIsPriceChanged);
            }
            $data = $this->getQuoteUpdatedData($quoteId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $data['messages'] = [
                ['type' => 'error', 'text' => __('Requested quote was not found.')]
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $data['messages'] = [
                [
                    'type' => 'error',
                    'text' => __('An error occurred on the server. %1', $e->getMessage())
                ]
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->getResultJson();
        $resultJson->setJsonData(json_encode($data, JSON_NUMERIC_CHECK));
        return $resultJson;
    }

    /**
     * Get quote update data.
     *
     * @param int $quoteId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuoteUpdatedData($quoteId)
    {
        $this->quoteData = [];
        $data = parent::getQuoteData();
        $quote = $this->quoteRepository->get($quoteId);
        $data['messages'] = $this->quoteUpdatesInfo->getMessages($quote);
        $this->quoteRepository->save($quote);

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function getQuote()
    {
        $quote = $this->quoteHelper->resolveCurrentQuote(true);
        if ($quote && !$this->canViewQuote($quote)) {
            return null;
        }

        return $quote;
    }
}
