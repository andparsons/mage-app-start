<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 declare(strict_types=1);

namespace Magento\NegotiableQuote\Model;

/**
 * Class for update quotes.
 */
class QuoteUpdater
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * @var \Magento\NegotiableQuote\Model\RuleChecker
     */
    private $ruleChecker;

    /**
     * @var \Magento\NegotiableQuote\Model\PriceChecker
     */
    private $priceChecker;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider
     */
    private $messageProvider;

    /**
     * @var bool
     */
    private $hasChanges = false;

    /**
     * @var bool
     */
    private $hasUnconfirmedChanges = false;

    /**
     * @var bool
     */
    private $needRecalculate = false;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier
     */
    private $messageApplier;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteItemsUpdater
     */
    private $quoteItemsUpdater;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param RuleChecker $ruleChecker
     * @param PriceChecker $priceChecker
     * @param \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider $messageProvider
     * @param \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier $messageApplier
     * @param QuoteItemsUpdater $quoteItemsUpdater
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $quoteItemManagement,
        RuleChecker $ruleChecker,
        PriceChecker $priceChecker,
        \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider $messageProvider,
        \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier $messageApplier,
        QuoteItemsUpdater $quoteItemsUpdater,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->restriction = $restriction;
        $this->quoteItemManagement = $quoteItemManagement;
        $this->ruleChecker = $ruleChecker;
        $this->priceChecker = $priceChecker;
        $this->messageProvider = $messageProvider;
        $this->messageApplier = $messageApplier;
        $this->quoteItemsUpdater = $quoteItemsUpdater;
        $this->date = $date;
    }

    /**
     * Update quote with $quoteId by $data.
     *
     * Log changes and save quote depending of $needLogChanges and $needSave arguments.
     *
     * @param int $quoteId
     * @param array $data
     * @param bool $needLogChanges [optional]
     * @param bool $needSave [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateQuote($quoteId, array $data, $needLogChanges = true, $needSave = false)
    {
        $this->quote = $this->getQuote($quoteId);
        $this->hasChanges = false;

        if (!$this->restriction->canSubmit()) {
            return false;
        }

        $oldRuleIds = $this->quote->getExtensionAttributes()->getNegotiableQuote()->getAppliedRuleIds();
        $oldPriceData = $this->priceChecker->collectItemsPriceData($this->quote);
        $oldCartPriceData = $this->priceChecker->collectItemsCartPriceData($this->quote);
        $oldDiscountAmount = $this->priceChecker->getTotalDiscount($this->quote);

        if (!empty($data)) {
            $this->updateItemsForQuote($data);
            $this->setExpirationPeriod($data);
            $this->setProposedPrice($data);
            $this->setShipping($data);
        } elseif ($this->quote->getExtensionAttributes()->getNegotiableQuote()->getNegotiatedPriceValue() == null) {
            $this->needRecalculate = true;
        }

        if ($this->hasChanges || $this->needRecalculate) {
            $this->updatePriceQuote($needSave);
            $this->quote->setTotalsCollectedFlag(false);
            $this->quote->collectTotals();
            $this->ruleChecker->checkIsDiscountRemoved($this->quote, $oldRuleIds, $needLogChanges);
            $priceChange = $this->priceChecker
                ->setIsProductPriceChanged($this->quote, $oldPriceData, $needLogChanges);
            $this->priceChecker->setIsCartPriceChanged($this->quote, $oldCartPriceData, $needLogChanges);
            $this->priceChecker->setIsDiscountChanged($this->quote, $oldDiscountAmount, $needLogChanges);
            $negotiableQuote = $this->retrieveNegotiableQuote($this->quote);
            $this->checkUnconfirmedChanges($priceChange, $data, $negotiableQuote);
            $this->addChangesMessages($this->quote);
        }

        return $this->hasChanges;
    }

    /**
     * Add messages if something has changed in quote.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    private function addChangesMessages(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $notifications = $this->messageProvider->getChangesMessages($quote);
        foreach ($notifications as $message) {
            if ($message) {
                $this->messages[] = ['type' => 'warning', 'text' => $message];
            }
        }
    }

    /**
     * Recalculate and update prices in quote.
     *
     * @param bool $needSave [optional]
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updatePriceQuote($needSave = false)
    {
        if ($this->needRecalculate) {
            $this->quoteItemManagement
                ->recalculateOriginalPriceTax($this->quote->getId(), false, true, false, $needSave);
        } else {
            $this->quoteItemManagement->updateQuoteItemsCustomPrices($this->quote->getId(), false);
        }
    }

    /**
     * Set expiration period in quote from $data.
     *
     * @param array $data
     * @return void
     */
    private function setExpirationPeriod(array $data)
    {
        if (isset($data['expiration_period'])) {
            $negotiableQuote = $this->retrieveNegotiableQuote($this->quote);
            $negotiableQuote->setExpirationPeriod($data['expiration_period']);
            $this->hasChanges = true;
        }
    }

    /**
     * Set proposed price in quote from $data.
     *
     * @param array $data
     * @return void
     */
    private function setProposedPrice(array $data)
    {
        if (!empty($data['proposed'])) {
            if ($data['proposed']['value'] === '') {
                $data['proposed']['value'] = null;
                $this->needRecalculate = true;
            }
            $negotiableQuote = $this->retrieveNegotiableQuote($this->quote);
            if (($data['proposed']['type'] != $negotiableQuote->getNegotiatedPriceType()
                || $data['proposed']['value'] != $negotiableQuote->getNegotiatedPriceValue())
                && empty($data['update'])
            ) {
                $this->hasUnconfirmedChanges = false;
            }
            $negotiableQuote->setNegotiatedPriceType($data['proposed']['type']);
            $negotiableQuote->setNegotiatedPriceValue($data['proposed']['value']);
            $this->hasChanges = true;
        }
    }

    /**
     * Set shipping data in quote from $data.
     *
     * @param array $data
     * @return void
     */
    private function setShipping(array $data)
    {
        if ($this->isShippingAddressDefined()) {
            $negotiableQuote = $this->retrieveNegotiableQuote($this->quote);

            if (isset($data['shipping']) && $negotiableQuote->getShippingPrice() != $data['shipping']) {
                $this->hasChanges = true;
                $negotiableQuote->setShippingPrice($data['shipping']);
            }

            if (!isset($data['shipping'])
                && $negotiableQuote->getShippingPrice() !== null
                && isset($data['shippingMethod'])
            ) {
                $this->hasChanges = true;
                $negotiableQuote->setShippingPrice(null);
            }

            if (!empty($data['shippingMethod'])
                && $data['shippingMethod'] != $this->quote->getShippingAddress()->getShippingMethod()
            ) {
                $this->setShippingMethod($data['shippingMethod']);
            }

            $this->quote->getShippingAddress()->setCollectShippingRates(true);
        }
    }

    /**
     * Set shipping method to negotiable quote.
     *
     * The shipping method will be saved later, when the quote is saved.
     * Setting the shipping method to the "shipping assignments" extension attribute overwrites the previously-set
     * value.
     *
     * @param string $shippingMethodCode
     * @return void
     */
    private function setShippingMethod($shippingMethodCode)
    {
        $this->hasChanges = true;
        $this->quote->getShippingAddress()->setShippingMethod($shippingMethodCode);

        if ($this->quote->getExtensionAttributes()
            && $this->quote->getExtensionAttributes()->getShippingAssignments()
        ) {
            $shippingAssignment = $this->quote->getExtensionAttributes()->getShippingAssignments()[0];
            $shippingAssignment->getShipping()->setMethod($shippingMethodCode);
        }
    }

    /**
     * Check whether the shipping address is defined.
     *
     * @return bool
     */
    private function isShippingAddressDefined()
    {
        return $this->quote->getShippingAddress()
        && $this->quote->getShippingAddress()->getPostcode();
    }

    /**
     * Update items for quote form $itemsData.
     *
     * @param array $itemsData
     * @return void
     */
    private function updateItemsForQuote(array $itemsData)
    {
        if (isset($itemsData['items']) || isset($itemsData['addItems']) || isset($itemsData['configuredSkus'])) {
            $this->clearQuoteAddressItemsCache();
            $result = $this->quoteItemsUpdater->updateItemsForQuote($this->quote, $itemsData);
            if ($result === true) {
                $this->hasChanges = true;
                if (!empty($itemsData['update']) || !isset($itemsData['update'])) {
                    $this->hasUnconfirmedChanges = true;
                }
            }
            $this->needRecalculate = $this->hasChanges || !empty($itemsData['recalcPrice']);
        }
    }

    /**
     * Clear quote addresses items cache.
     *
     * When a quote has a 'trigger_recollect' flag, clear the cache to add new items with correct prices.
     *
     * @return void
     */
    private function clearQuoteAddressItemsCache()
    {
        if ($this->quote->getData('trigger_recollect')) {
            foreach ($this->quote->getAllAddresses() as $address) {
                $address->unsetData('cached_items_all');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Retrieve negotiable quote from quote.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|null
     */
    private function retrieveNegotiableQuote(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $negotiableQuote = null;

        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()) {
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        }

        return $negotiableQuote;
    }

    /**
     * Initialize quote model instance.
     *
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuote($quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $negotiableQuote = $this->retrieveNegotiableQuote($quote);
        if ($negotiableQuote === null
            || !$negotiableQuote->getIsRegularQuote()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        }

        return $quote;
    }

    /**
     * Check if quote has unconfirmed changes.
     *
     * @param bool $priceChange
     * @param array $data
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface $negotiableQuote
     * @return void
     */
    private function checkUnconfirmedChanges(
        $priceChange,
        array $data,
        \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface $negotiableQuote
    ) {
        if ($priceChange && !empty($data['update']) && $data['update'] == 1) {
            $this->hasUnconfirmedChanges = true;
        }
        if ($negotiableQuote->getNegotiatedPriceValue() === null) {
            $this->hasUnconfirmedChanges = false;
        }
        if ($this->hasUnconfirmedChanges && $negotiableQuote->getNegotiatedPriceValue() !== null) {
            $this->messageApplier->removeMessage(
                $negotiableQuote,
                \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::ITEMS_CHANGED,
                true
            );
        }
        $negotiableQuote->setHasUnconfirmedChanges($this->hasUnconfirmedChanges);
    }

    /**
     * Update created and updated date in quote to current date.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function updateCurrentDate(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $date = $this->date->gmtDate();
        $quote->setUpdatedAt($date)
            ->setCreatedAt($date);
        return $quote;
    }

    /**
     * Update quote items by $cartData.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param array $cartData [optional]
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function updateQuoteItemsByCartData(\Magento\Quote\Api\Data\CartInterface $quote, array $cartData = [])
    {
        return $this->quoteItemsUpdater->updateQuoteItemsByCartData($quote, $cartData);
    }
}
