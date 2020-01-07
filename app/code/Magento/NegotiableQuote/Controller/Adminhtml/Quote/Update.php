<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Model\QuoteUpdater;
use Magento\NegotiableQuote\Model\Quote\Currency as QuoteCurrency;

/**
 * Update quote items' information.
 */
class Update extends \Magento\NegotiableQuote\Controller\Adminhtml\Quote implements HttpPostActionInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdater
     */
    protected $quoteUpdater;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdatesInfo
     */
    protected $quoteUpdatesInfo;

    /**
     * @var \Magento\AdvancedCheckout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Currency
     */
    protected $quoteCurrency;

    /**
     * @var array
     */
    protected $quoteData;

    /**
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param QuoteUpdater $quoteUpdater
     * @param \Magento\AdvancedCheckout\Model\CartFactory $cartFactory
     * @param \Magento\NegotiableQuote\Model\QuoteUpdatesInfo $quoteUpdatesInfo
     * @param QuoteCurrency $quoteCurrency
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        QuoteUpdater $quoteUpdater,
        \Magento\AdvancedCheckout\Model\CartFactory $cartFactory,
        \Magento\NegotiableQuote\Model\QuoteUpdatesInfo $quoteUpdatesInfo,
        QuoteCurrency $quoteCurrency
    ) {
        parent::__construct(
            $context,
            $logger,
            $quoteRepository,
            $negotiableQuoteManagement
        );
        $this->quoteUpdater = $quoteUpdater;
        $this->cartFactory = $cartFactory;
        $this->quoteUpdatesInfo = $quoteUpdatesInfo;
        $this->quoteCurrency = $quoteCurrency;
    }

    /**
     * Update quote items.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $this->quoteData = $this->prepareQuoteData();
        try {
            $this->quoteCurrency->updateQuoteCurrency($quoteId);
            $this->quoteUpdater->updateQuote($quoteId, $this->quoteData, false);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Exception occurred during update quote'));
        }

        $data = $this->getQuoteData();
        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->getResultJson();
        $response->setJsonData(json_encode($data, JSON_NUMERIC_CHECK));
        return $response;
    }

    /**
     * Get quote updates data.
     *
     * @return array
     */
    protected function getQuoteData()
    {
        $quote = $this->getQuote();
        $data = [];
        if ($quote) {
            $data = $this->quoteUpdatesInfo->getQuoteUpdatedData($quote, $this->quoteData);
            $data['hasFailedItems'] = $this->hasFailedItems();
            $data['messages'] = $this->quoteUpdater->getMessages();
        } else {
            $data['messages'] = [
                ['type' => 'error', 'text' => __('Requested quote was not found.')]
            ];
        }

        return $data;
    }

    /**
     * Get is cart has failed items.
     *
     * @return bool
     */
    private function hasFailedItems()
    {
        $cart = $this->cartFactory->create()->setSession($this->_session);
        $failedItems = $cart->getFailedItems();
        return count($failedItems) > 0;
    }

    /**
     * Prepare quote data.
     *
     * @return array
     */
    protected function prepareQuoteData()
    {
        $quoteData = $this->getRequest()->getParam('quote');

        if (is_array($quoteData) && !isset($quoteData['items'])) {
            $quoteData['items'] = [];
        }

        return (array)$quoteData;
    }
}
