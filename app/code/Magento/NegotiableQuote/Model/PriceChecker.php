<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\NegotiableQuote\Model\Discount\StateChanges\Applier;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class for checking and logging of changes in negotiable quote items prices.
 */
class PriceChecker
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier
     */
    private $messageApplier;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    protected $negotiableQuoteItemManagement;

    /**
     * RuleChecker constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param HistoryManagementInterface $historyManagement
     * @param \Magento\NegotiableQuote\Model\Quote\TotalsFactory $quoteTotalsFactory
     * @param Applier $messageApplier
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        HistoryManagementInterface $historyManagement,
        \Magento\NegotiableQuote\Model\Quote\TotalsFactory $quoteTotalsFactory,
        Applier $messageApplier,
        \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->historyManagement = $historyManagement;
        $this->quoteTotalsFactory = $quoteTotalsFactory;
        $this->messageApplier = $messageApplier;
        $this->negotiableQuoteItemManagement = $negotiableQuoteItemManagement;
    }

    /**
     * Set quote for changed original prices of products.
     *
     * @param CartInterface $quote
     * @param array $oldPriceData
     * @param bool $needLog [optional]
     * @param bool $needMessage [optional]
     * @return bool
     */
    public function setIsProductPriceChanged(
        CartInterface $quote,
        array $oldPriceData,
        $needLog = true,
        $needMessage = true
    ) {
        $isChanges = false;
        if ($quote->getId()) {
            $newPriceData = $this->collectItemsPriceData($quote);
            if (is_array($newPriceData) && (count($newPriceData) > 0)) {
                $pricesChanged = $this->priceDiff($oldPriceData, $newPriceData);
                if ($pricesChanged) {
                    if ($needLog) {
                        $values = [];
                        $ids = $this->retrieveIdsForSkus($quote);
                        foreach ($pricesChanged as $sku => $priceValues) {
                            $valueToAdd['old_value'] = strip_tags(
                                $this->priceCurrency->format(
                                    $priceValues['old_value'],
                                    true,
                                    PriceCurrencyInterface::DEFAULT_PRECISION,
                                    null,
                                    $quote->getCurrency()->getBaseCurrencyCode()
                                )
                            );
                            $valueToAdd['new_value'] = strip_tags(
                                $this->priceCurrency->format(
                                    $priceValues['new_value'],
                                    true,
                                    PriceCurrencyInterface::DEFAULT_PRECISION,
                                    null,
                                    $quote->getCurrency()->getBaseCurrencyCode()
                                )
                            );
                            $valueToAdd['field_subtitle'] = __('Catalog Price: ')->__toString();
                            $valueToAdd['field_id'] = 'catalog_price';
                            $value['product_sku'] = $sku;
                            $value['product_id'] = $ids[$sku];
                            $value['values'] = [$valueToAdd];
                            $value['field_id'] = 'product_' . $sku;
                            $values[] = $value;
                        }
                        $this->historyManagement->addCustomLog(
                            $quote->getId(),
                            $values,
                            false,
                            true
                        );
                    }
                    if ($needMessage) {
                        $this->messageApplier->setHasItemChanges($quote);
                    }
                    $this->setIsCustomerPriceChanged($quote);
                    $isChanges = true;
                }
            }
        }
        return $isChanges;
    }

    /**
     * Retrieve associative array with product Sku as key and id as value for the quote items.
     *
     * @param CartInterface $quote
     * @return array
     */
    private function retrieveIdsForSkus(CartInterface $quote)
    {
        $ids = [];
        if ($quote !== null) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllVisibleItems() as $item) {
                $ids[$item->getProduct()->getSku()] = $item->getProduct()->getId();
            }
        }

        return $ids;
    }

    /**
     * Set for discount changes.
     *
     * @param CartInterface $quote
     * @param float|int $oldDiscountAmount
     * @param bool $needLog [optional]
     * @return bool
     */
    public function setIsDiscountChanged(CartInterface $quote, $oldDiscountAmount, $needLog = true)
    {
        $isChanges = false;
        if ($quote->getId()) {
            $newDiscountAmount = $this->getTotalDiscount($quote);
            if ($newDiscountAmount != $oldDiscountAmount && ($newDiscountAmount != 0)) {
                if ($needLog) {
                    $changeData['field_title'] = __('Quote Discount')->__toString();
                    $valueToAdd = [
                        'field_subtitle' => __('Discount amount: ')->__toString(),
                        'field_id' => 'amount',
                        'new_value' => strip_tags(
                            $this->priceCurrency->format(
                                $newDiscountAmount,
                                true,
                                PriceCurrencyInterface::DEFAULT_PRECISION,
                                null,
                                $quote->getCurrency()->getBaseCurrencyCode()
                            )
                        )
                    ];
                    if ($oldDiscountAmount != 0) {
                        $valueToAdd['old_value'] = strip_tags(
                            $this->priceCurrency->format(
                                $oldDiscountAmount,
                                true,
                                PriceCurrencyInterface::DEFAULT_PRECISION,
                                null,
                                $quote->getCurrency()->getBaseCurrencyCode()
                            )
                        );
                    }
                    $changeData['values'] = [$valueToAdd];
                    $changeData['field_id'] = 'discount';
                    $this->historyManagement->addCustomLog(
                        $quote->getId(),
                        [$changeData],
                        false,
                        true
                    );
                }
                $this->messageApplier->setIsDiscountChanged($quote);
                $isChanges = true;
            }
        }
        return $isChanges;
    }

    /**
     * Collect original price data for each item of the quote.
     *
     * @param CartInterface $quote
     * @return array
     */
    public function collectItemsPriceData(CartInterface $quote)
    {
        $priceData = [];
        if ($quote !== null) {
            /** @var CartItemInterface $item */
            foreach ($quote->getAllVisibleItems() as $item) {
                $priceData[$item->getProduct()->getSku()] = $this
                    ->retrieveItemData($item, NegotiableQuoteItemInterface::ORIGINAL_PRICE);
            }
        }

        return $priceData;
    }

    /**
     * Get total discount for quote.
     *
     * @param CartInterface $quote
     * @return float|int
     */
    public function getTotalDiscount(CartInterface $quote)
    {
        $totals = $this->quoteTotalsFactory->create(['quote' => $quote]);

        return $totals->getCartTotalDiscount();
    }

    /**
     * Process difference between old and new price data.
     *
     * @param array $oldPriceData
     * @param array $newPriceData
     * @return array
     */
    private function priceDiff(array $oldPriceData, array $newPriceData)
    {
        $diff = [];
        foreach ($newPriceData as $sku => $price) {
            if (isset($oldPriceData[$sku]) && $oldPriceData[$sku] && ($price != $oldPriceData[$sku])) {
                $diff[$sku] = [
                    'old_value' => $oldPriceData[$sku],
                    'new_value' => $price
                ];
            }
        }
        return $diff;
    }

    /**
     * Retrieve data from item by key.
     *
     * @param CartItemInterface $item
     * @param string $key
     * @return float|int
     */
    private function retrieveItemData(CartItemInterface $item, $key)
    {
        $price = 0;

        if ($item->getExtensionAttributes() !== null
            && $item->getExtensionAttributes()->getNegotiableQuoteItem() !== null) {
            $price = $item->getExtensionAttributes()
                ->getNegotiableQuoteItem()
                ->getData($key);
        }

        return $price;
    }

    /**
     * Collect cart price data for each item of the quote.
     *
     * @param CartInterface $quote
     * @return array
     */
    public function collectItemsCartPriceData(CartInterface $quote)
    {
        $priceData = [];
        if ($quote !== null) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllVisibleItems() as $item) {
                $priceData[$item->getProduct()->getSku()] =
                    $this->negotiableQuoteItemManagement->getOriginalPriceByItem($item);
            }
        }

        return $priceData;
    }

    /**
     * Set quote for changed cart prices of products.
     *
     * @param CartInterface $quote
     * @param array $oldPriceData
     * @param bool $needLog [optional]
     * @return bool
     */
    public function setIsCartPriceChanged(
        CartInterface $quote,
        array $oldPriceData,
        $needLog = true
    ) {
        $isChanges = false;
        if ($quote->getId()) {
            $newPriceData = $this->collectItemsCartPriceData($quote);
            if (is_array($newPriceData) && (count($newPriceData) > 0)) {
                $pricesChanged = $this->priceDiff($oldPriceData, $newPriceData);
                if ($pricesChanged) {
                    if ($needLog) {
                        $ids = $this->retrieveIdsForSkus($quote);
                        $values = [];
                        foreach ($pricesChanged as $sku => $priceValues) {
                            $valuesToAdd['old_value'] = strip_tags(
                                $this->priceCurrency->format(
                                    $priceValues['old_value'],
                                    true,
                                    PriceCurrencyInterface::DEFAULT_PRECISION,
                                    null,
                                    $quote->getCurrency()->getBaseCurrencyCode()
                                )
                            );
                            $valuesToAdd['new_value'] = strip_tags(
                                $this->priceCurrency->format(
                                    $priceValues['new_value'],
                                    true,
                                    PriceCurrencyInterface::DEFAULT_PRECISION,
                                    null,
                                    $quote->getCurrency()->getBaseCurrencyCode()
                                )
                            );
                            $valuesToAdd['field_subtitle'] = __('Cart Price: ')->__toString();
                            $valuesToAdd['field_id'] = 'cart_price';
                            $value['product_sku'] = $sku;
                            $value['product_id'] = $ids[$sku];
                            $value['values'] = [$valuesToAdd];
                            $value['field_id'] = 'product_' . $sku;
                            $values[] = $value;
                        }
                        $this->historyManagement->addCustomLog(
                            $quote->getId(),
                            $values,
                            false,
                            true
                        );
                    }

                    $this->setIsCustomerPriceChanged($quote);
                    $isChanges = true;
                }
            }
        }
        return $isChanges;
    }

    /**
     * Get total original tax for quote.
     *
     * @param CartInterface $quote
     * @return float|int
     */
    public function getSubtotalOriginalTax(CartInterface $quote)
    {
        $totals = $this->quoteTotalsFactory->create(['quote' => $quote]);

        return $totals->getOriginalTaxValue();
    }

    /**
     * Get tax for shipping.
     *
     * @param CartInterface $quote
     * @return float|int
     */
    public function getShippingTax(CartInterface $quote)
    {
        $totals = $this->quoteTotalsFactory->create(['quote' => $quote]);

        return $totals->getShippingTaxValue();
    }

    /**
     * Set quote for changed total original tax.
     *
     * @param CartInterface $quote
     * @param float $oldTax
     * @return bool
     */
    public function setIsSubtotalOriginalTaxChanged(CartInterface $quote, $oldTax)
    {
        $isChanges = false;
        if ($quote->getId()) {
            $newTax = $this->getSubtotalOriginalTax($quote);
            if ($oldTax != $newTax) {
                $isChanges = $this->setIsCustomerPriceChanged($quote);
            }
        }
        return $isChanges;
    }

    /**
     * Set quote for changed tax for shipping.
     *
     * @param CartInterface $quote
     * @param float $oldTax
     * @return bool
     */
    public function setIsShippingTaxChanged(CartInterface $quote, $oldTax)
    {
        $isChanges = false;
        if ($quote->getId()) {
            $newTax = $this->getShippingTax($quote);
            if ($oldTax != $newTax) {
                if ($quote->getExtensionAttributes() !== null
                    && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
                    && $quote->getExtensionAttributes()->getNegotiableQuote()->getNegotiatedPriceValue() !== null
                    && $quote->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice() !== null
                ) {
                    $quote->getExtensionAttributes()->getNegotiableQuote()->setIsShippingTaxChanged(true);
                    $isChanges = true;
                }
            }
        }
        return $isChanges;
    }

    /**
     * Set customer price changed.
     *
     * @param CartInterface $quote
     * @return bool
     */
    private function setIsCustomerPriceChanged(CartInterface $quote)
    {
        $isChanges = false;
        if ($quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getNegotiatedPriceValue() !== null
        ) {
            $quote->getExtensionAttributes()->getNegotiableQuote()->setIsCustomerPriceChanged(true);
            $isChanges = true;
        }

        return $isChanges;
    }
}
