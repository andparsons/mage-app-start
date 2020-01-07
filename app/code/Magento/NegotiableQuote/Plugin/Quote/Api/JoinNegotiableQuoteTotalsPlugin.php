<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Api;

use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterface;
use Magento\NegotiableQuote\Model\Quote\TotalsFactory;
use Magento\Quote\Api\Data\TotalsItemExtensionFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterfaceFactory;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Plugin for adding negotiable quote totals and items totals in TotalsInterface.
 */
class JoinNegotiableQuoteTotalsPlugin
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\Data\TotalsExtensionFactory
     */
    private $totalsExtensionFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterfaceFactory
     */
    private $negotiableQuoteTotalsFactory;

    /**
     * @var \Magento\Quote\Api\Data\TotalsItemExtensionFactory
     */
    private $totalsItemExtensionFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterfaceFactory
     */
    private $negotiableQuoteItemTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param TotalsExtensionFactory $totalsExtensionFactory
     * @param NegotiableQuoteTotalsInterfaceFactory $negotiableQuoteTotalsFactory
     * @param TotalsItemExtensionFactory $totalsItemExtensionFactory
     * @param NegotiableQuoteItemTotalsInterfaceFactory $negotiableQuoteItemTotalsFactory
     * @param TotalsFactory $quoteTotalsFactory
     * @param TaxConfig $taxConfig
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        TotalsExtensionFactory $totalsExtensionFactory,
        NegotiableQuoteTotalsInterfaceFactory $negotiableQuoteTotalsFactory,
        TotalsItemExtensionFactory $totalsItemExtensionFactory,
        NegotiableQuoteItemTotalsInterfaceFactory $negotiableQuoteItemTotalsFactory,
        TotalsFactory $quoteTotalsFactory,
        TaxConfig $taxConfig
    ) {
        $this->cartRepository = $cartRepository;
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->negotiableQuoteTotalsFactory = $negotiableQuoteTotalsFactory;
        $this->totalsItemExtensionFactory = $totalsItemExtensionFactory;
        $this->negotiableQuoteItemTotalsFactory = $negotiableQuoteItemTotalsFactory;
        $this->quoteTotalsFactory = $quoteTotalsFactory;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Join negotiable quote totals and items totals to TotalsInterface.
     *
     * @param CartTotalRepositoryInterface $subject
     * @param TotalsInterface $result
     * @param int $cartId
     * @return TotalsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGet(
        CartTotalRepositoryInterface $subject,
        TotalsInterface $result,
        $cartId
    ) {
        $quote = $this->cartRepository->get($cartId, ['*']);
        if ($quote && $quote->getExtensionAttributes()
            && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
        ) {
            $this->joinExtensionAttributes($result);
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            /** @var \Magento\NegotiableQuote\Model\Quote\Totals $totals */
            $totals = $this->quoteTotalsFactory->create(['quote' => $quote]);
            $data = [
                NegotiableQuoteTotalsInterface::QUOTE_STATUS => $negotiableQuote->getStatus(),
                NegotiableQuoteTotalsInterface::CREATED_AT => $quote->getCreatedAt(),
                NegotiableQuoteTotalsInterface::UPDATED_AT => $quote->getUpdatedAt(),
                NegotiableQuoteTotalsInterface::CUSTOMER_GROUP => $quote->getCustomer()->getGroupId(),
                NegotiableQuoteTotalsInterface::ITEMS_COUNT => $quote->getItemsCount(),
                NegotiableQuoteTotalsInterface::BASE_TO_QUOTE_RATE => $quote->getCurrency()->getBaseToQuoteRate(),
                NegotiableQuoteTotalsInterface::COST_TOTAL => $totals->getTotalCost(true),
                NegotiableQuoteTotalsInterface::BASE_COST_TOTAL => $totals->getTotalCost(),
                NegotiableQuoteTotalsInterface::BASE_ORIGINAL_TOTAL => $totals->getCatalogTotalPrice(),
                NegotiableQuoteTotalsInterface::BASE_ORIGINAL_TAX => $totals->getOriginalTaxValue(),
                NegotiableQuoteTotalsInterface::BASE_ORIGINAL_PRICE_INCL_TAX => $totals->getCatalogTotalPriceWithTax(),
                NegotiableQuoteTotalsInterface::ORIGINAL_TOTAL => $totals->getCatalogTotalPrice(true),
                NegotiableQuoteTotalsInterface::ORIGINAL_TAX => $totals->getOriginalTaxValue(true),
                NegotiableQuoteTotalsInterface::ORIGINAL_PRICE_INCL_TAX => $totals->getCatalogTotalPriceWithTax(true),
                NegotiableQuoteTotalsInterface::NEGOTIATED_PRICE_TYPE => $negotiableQuote->getNegotiatedPriceType(),
                NegotiableQuoteTotalsInterface::NEGOTIATED_PRICE_VALUE => $negotiableQuote->getNegotiatedPriceValue()
            ];
            /** @var \Magento\NegotiableQuote\Model\NegotiableQuoteTotals $negotiableTotals */
            $negotiableTotals = $this->negotiableQuoteTotalsFactory->create(['data' => $data]);
            $result->getExtensionAttributes()->setNegotiableQuoteTotals($negotiableTotals);

            $quoteItems = $quote->getItems();
            $rate = $quote->getCurrency()->getBaseToQuoteRate();
            foreach ($result->getItems() as $totalsItem) {
                $quoteItem = null;
                foreach ($quoteItems as $item) {
                    if ($item->getItemId() == $totalsItem->getItemId()) {
                        $quoteItem = $item;
                        break;
                    }
                }

                $negotiableItem = $quoteItem->getExtensionAttributes()->getNegotiableQuoteItem();
                $cartPrice = $negotiableItem->getOriginalPrice() - $negotiableItem->getOriginalDiscountAmount();
                $cartPriceInclTax = $cartPrice;
                $catalogPriceInclTax = $negotiableItem->getOriginalPrice();
                if (!$this->taxConfig->priceIncludesTax($quote->getStoreId())) {
                    $cartPriceInclTax += $negotiableItem->getOriginalTaxAmount();
                    $catalogPriceInclTax += $negotiableItem->getOriginalTaxAmount();
                }
                $data = [
                    NegotiableQuoteItemTotalsInterface::COST => $totals->getItemCost($quoteItem),
                    NegotiableQuoteItemTotalsInterface::BASE_CATALOG_PRICE => $negotiableItem->getOriginalPrice(),
                    NegotiableQuoteItemTotalsInterface::CATALOG_PRICE =>
                        round($negotiableItem->getOriginalPrice() * $rate, 2),
                    NegotiableQuoteItemTotalsInterface::BASE_CATALOG_PRICE_INCL_TAX => $catalogPriceInclTax,
                    NegotiableQuoteItemTotalsInterface::CATALOG_PRICE_INCL_TAX =>
                        round($catalogPriceInclTax * $rate, 2),
                    NegotiableQuoteItemTotalsInterface::BASE_CART_PRICE => $cartPrice,
                    NegotiableQuoteItemTotalsInterface::CART_PRICE => round($cartPrice * $rate, 2),
                    NegotiableQuoteItemTotalsInterface::BASE_CART_TAX => $negotiableItem->getOriginalTaxAmount(),
                    NegotiableQuoteItemTotalsInterface::CART_TAX =>
                        round($negotiableItem->getOriginalTaxAmount() * $rate, 2),
                    NegotiableQuoteItemTotalsInterface::BASE_CART_PRICE_INCL_TAX => $cartPriceInclTax,
                    NegotiableQuoteItemTotalsInterface::CART_PRICE_INCL_TAX => round($cartPriceInclTax * $rate, 2)
                ];
                $negotiableItemTotals = $this->negotiableQuoteItemTotalsFactory->create(['data' => $data]);
                $totalsItem->getExtensionAttributes()->setNegotiableQuoteItemTotals($negotiableItemTotals);
            }

            // Set negotiable quote total price as quote subtotal. It's needed to show correct negotiable quote price
            // during checkout steps
            $result->setSubtotal($totals->getSubtotalWithoutTax(true))
                ->setBaseSubtotal($totals->getSubtotalWithoutTax())
                ->setSubtotalInclTax($totals->getSubtotalWithTax(true));
        }
        return $result;
    }

    /**
     * Join Extension Attributes to quote totals and quote item totals.
     *
     * @param TotalsInterface $totals
     * @return void
     */
    private function joinExtensionAttributes(TotalsInterface $totals)
    {
        if (!$totals->getExtensionAttributes()) {
            $totals->setExtensionAttributes($this->totalsExtensionFactory->create());
        }
        foreach ($totals->getItems() as $totalsItem) {
            if (!$totalsItem->getExtensionAttributes()) {
                $totalsItem->setExtensionAttributes(
                    $this->totalsItemExtensionFactory->create()
                );
            }
        }
    }
}
