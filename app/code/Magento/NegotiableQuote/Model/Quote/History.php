<?php

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\Quote\Api\Data\CartInterface as Quote;
use Magento\Framework\DataObject;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\PriceChecker;
use Magento\NegotiableQuote\Model\RuleChecker;
use Magento\NegotiableQuote\Model\HistoryManagementInterface;

/**
 * Class History
 */
class History
{
    /**
     * @var \Magento\NegotiableQuote\Model\PriceChecker
     */
    private $priceChecker;

    /**
     * @var \Magento\NegotiableQuote\Model\RuleChecker
     */
    private $ruleChecker;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * History constructor.
     * @param PriceChecker $priceChecker
     * @param RuleChecker $ruleChecker
     * @param HistoryManagementInterface $historyManagement
     */
    public function __construct(
        PriceChecker $priceChecker,
        RuleChecker $ruleChecker,
        HistoryManagementInterface $historyManagement
    ) {
        $this->historyManagement = $historyManagement;
        $this->priceChecker = $priceChecker;
        $this->ruleChecker = $ruleChecker;
    }

    /**
     * Collect quote data.
     *
     * @param Quote $quote
     * @return DataObject
     */
    public function collectOldDataFromQuote(Quote $quote)
    {
        $data = new DataObject();

        if ($quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null) {
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $data->setData('old_rule_ids', $negotiableQuote->getAppliedRuleIds());
        }

        $data->setData('old_price_data', $this->priceChecker->collectItemsPriceData($quote));
        $data->setData('old_cart_price_data', $this->priceChecker->collectItemsCartPriceData($quote));
        $data->setData('old_discount_amount', $this->priceChecker->getTotalDiscount($quote));

        return $data;
    }

    /**
     * Collect tax data.
     *
     * @param Quote $quote
     * @return DataObject
     */
    public function collectTaxDataFromQuote(Quote $quote)
    {
        $data = new DataObject();
        $data->setData('subtotal_tax', $this->priceChecker->getSubtotalOriginalTax($quote));
        $data->setData('shipping_tax', $this->priceChecker->getShippingTax($quote));

        return $data;
    }

    /**
     *  Check quote prices and discount and log changes.
     *
     * @param Quote $quote
     * @param DataObject $oldData
     * @return DataObject
     */
    public function checkPricesAndDiscounts(Quote $quote, DataObject $oldData)
    {
        $resultData = new DataObject();
        $resultData->setData('is_tax_changed', $this->ruleChecker->checkIsDiscountRemoved(
            $quote,
            $oldData->getOldRuleIds()
        ));
        $resultData->setData('is_price_changed', $this->priceChecker->setIsProductPriceChanged(
            $quote,
            $oldData->getOldPriceData()
        ));
        $this->priceChecker->setIsCartPriceChanged($quote, $oldData->getOldCartPriceData());
        $resultData->setData('is_discount_changed', $this->priceChecker->setIsDiscountChanged(
            $quote,
            $oldData->getOldDiscountAmount()
        ));
        $resultData->setData(
            'is_changed',
            $resultData->getIsTaxChanged() || $resultData->getIsPriceChanged() || $resultData->getIsDiscountChanged()
        );

        return $resultData;
    }

    /**
     * Check changes of tax data.
     *
     * @param Quote $quote
     * @param DataObject $taxData
     * @return DataObject
     */
    public function checkTaxes(Quote $quote, DataObject $taxData)
    {
        $resultData = new DataObject();
        $resultData->setData('is_tax_changed', $this->priceChecker->setIsSubtotalOriginalTaxChanged(
            $quote,
            $taxData->getSubtotalTax()
        ));
        $resultData->setData('is_shipping_tax_changed', $this->priceChecker->setIsShippingTaxChanged(
            $quote,
            $taxData->getShippingTax()
        ));

        return $resultData;
    }

    /**
     * @param NegotiableQuoteInterface $negotiableQuote
     * @return void
     */
    public function removeAdminMessage(NegotiableQuoteInterface $negotiableQuote)
    {
        $messages = $negotiableQuote->getNotifications() % NegotiableQuoteInterface::DISCOUNT_ADMIN_MODE;
        $negotiableQuote->setNotifications($messages);
    }

    /**
     * @param NegotiableQuoteInterface $negotiableQuote
     * @return void
     */
    public function removeFrontMessage(NegotiableQuoteInterface $negotiableQuote)
    {
        $adminMode = NegotiableQuoteInterface::DISCOUNT_ADMIN_MODE;
        $messages = floor($negotiableQuote->getNotifications() / $adminMode) * $adminMode;
        $negotiableQuote->setNotifications($messages);
    }

    /**
     * Add history log with status "closed" and update draft log
     *
     * @param int $quoteId
     * @return void
     */
    public function closeLog($quoteId)
    {
        $this->historyManagement->closeLog($quoteId);
        $this->historyManagement->updateDraftLogs($quoteId);
    }

    /**
     * Add history log only with quote status changes and update draft log
     *
     * @param int $quoteId
     * @param bool $isSeller
     * @return void
     */
    public function updateStatusLog($quoteId, $isSeller = false)
    {
        $this->historyManagement->updateStatusLog($quoteId, $isSeller);
        $this->historyManagement->updateDraftLogs($quoteId);
    }

    /**
     * Add history log with status "updated" and snapshot quote data
     *
     * @param int $quoteId
     * @param bool $isSeller
     * @param string $status
     * @return void
     */
    public function updateLog($quoteId, $isSeller = false, $status = '')
    {
        $this->historyManagement->updateLog($quoteId, $isSeller, $status);
        $this->historyManagement->updateDraftLogs($quoteId);
    }

    /**
     * Add history log with status "created" and snapshot data of the new quote
     *
     * @param int $quoteId
     * @return void
     */
    public function createLog($quoteId)
    {
        $this->historyManagement->createLog($quoteId);
    }
}
