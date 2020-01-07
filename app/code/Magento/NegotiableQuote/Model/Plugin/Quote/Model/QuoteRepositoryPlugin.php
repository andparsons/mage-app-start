<?php

namespace Magento\NegotiableQuote\Model\Plugin\Quote\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteRepository;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid;
use Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Plugin for saving negotiable quote extension attribute and providing negotiable quote on get method.
 */
class QuoteRepositoryPlugin
{
    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteRepository
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory
     */
    private $negotiableQuoteItemFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem
     */
    private $negotiableQuoteItemResource;

    /**
     * @param NegotiableQuoteRepository $negotiableQuoteRepository
     * @param QuoteGrid $quoteGrid
     * @param NegotiableQuoteItemFactory $negotiableQuoteItemFactory
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\NegotiableQuote\Model\Quote\TotalsFactory $quoteTotalsFactory
     * @param \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem $negotiableQuoteItemResource
     */
    public function __construct(
        NegotiableQuoteRepository $negotiableQuoteRepository,
        QuoteGrid $quoteGrid,
        NegotiableQuoteItemFactory $negotiableQuoteItemFactory,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\NegotiableQuote\Model\Quote\TotalsFactory $quoteTotalsFactory,
        \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem $negotiableQuoteItemResource
    ) {
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteGrid = $quoteGrid;
        $this->negotiableQuoteItemFactory = $negotiableQuoteItemFactory;
        $this->extensionFactory = $extensionFactory;
        $this->quoteTotalsFactory = $quoteTotalsFactory;
        $this->negotiableQuoteItemResource = $negotiableQuoteItemResource;
    }

    /**
     * After quote save plugin.
     *
     * @param CartRepositoryInterface $subject
     * @param \Closure $proceed
     * @param CartInterface $quote
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(CartRepositoryInterface $subject, \Closure $proceed, CartInterface $quote)
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes
            && $extensionAttributes->getNegotiableQuote()
            && $extensionAttributes->getNegotiableQuote()->getIsRegularQuote()) {
            $quote->setIsActive(false);
        }
        $proceed($quote);
        $negotiableQuoteToUpdate = $this->getNegotiableQuoteToBeUpdated($quote);

        if ($negotiableQuoteToUpdate && $negotiableQuoteToUpdate->getQuoteId()) {
            $negotiableQuoteItems = [];
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            foreach ($quote->getAllItems() as $quoteItem) {
                $this->calculateOriginalPrices($quoteItem);
                $negotiableQuoteItems[] = $quoteItem->getExtensionAttributes()->getNegotiableQuoteItem();
            }
            $this->negotiableQuoteItemResource->saveList($negotiableQuoteItems);
            $this->setNegotiableQuotePrices($negotiableQuoteToUpdate, $quote);
            $this->negotiableQuoteRepository->save($negotiableQuoteToUpdate);
            $this->quoteGrid->refresh($quote);
        }
    }

    /**
     * Set negotiable quote prices.
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param CartInterface $quote
     * @return void
     */
    private function setNegotiableQuotePrices(
        NegotiableQuoteInterface $negotiableQuote,
        CartInterface $quote
    ) {
        $totals = $this->quoteTotalsFactory->create(['quote' => $quote]);
        $negotiableQuote->setData(NegotiableQuoteInterface::ORIGINAL_TOTAL_PRICE, $totals->getCatalogTotalPrice(true));
        $negotiableQuote->setData(NegotiableQuoteInterface::BASE_ORIGINAL_TOTAL_PRICE, $totals->getCatalogTotalPrice());
        if ($negotiableQuote->getStatus() !== NegotiableQuoteInterface::STATUS_CREATED) {
            $negotiableQuote->setData(NegotiableQuoteInterface::BASE_NEGOTIATED_TOTAL_PRICE, $totals->getSubtotal());
            $negotiableQuote->setData(NegotiableQuoteInterface::NEGOTIATED_TOTAL_PRICE, $totals->getSubtotal(true));
        } else {
            $negotiableQuote
                ->setData(NegotiableQuoteInterface::BASE_NEGOTIATED_TOTAL_PRICE, $totals->getCatalogTotalPrice());
            $negotiableQuote
                ->setData(NegotiableQuoteInterface::NEGOTIATED_TOTAL_PRICE, $totals->getCatalogTotalPrice(true));
        }
    }

    /**
     * There is no need to be active for negotiable quote.
     *
     * @param CartRepositoryInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param array $sharedStoreIds [optional]
     * @return CartInterface
     */
    public function aroundGetActive(
        CartRepositoryInterface $subject,
        \Closure $proceed,
        $cartId,
        array $sharedStoreIds = []
    ) {
        $quote = $subject->get($cartId, $sharedStoreIds);

        if ($quote !== null
            && $quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
            && !empty($quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote())) {
            $result = $quote;
        } else {
            $result = $proceed($cartId, $sharedStoreIds);
        }

        return $result;
    }

    /**
     * Return negotiable quotes that needs to be updated.
     *
     * @param CartInterface $quote
     *
     * @return NegotiableQuoteInterface|bool $negotiableQuoteToUpdate
     */
    private function getNegotiableQuoteToBeUpdated(CartInterface $quote)
    {
        $negotiableQuoteToUpdate = false;
        $extensionAttributes = $quote->getExtensionAttributes();

        if ($extensionAttributes && $extensionAttributes->getNegotiableQuote()) {
            $negotiableQuoteToUpdate = $extensionAttributes->getNegotiableQuote();
            if (!$negotiableQuoteToUpdate->getQuoteId() && $negotiableQuoteToUpdate->getIsRegularQuote()) {
                $negotiableQuoteToUpdate->setQuoteId($quote->getId());
            }
        }

        return $negotiableQuoteToUpdate;
    }

    /**
     * Calculate original prices of quote item.
     *
     * @param CartItemInterface $quoteItem
     * @return void
     */
    private function calculateOriginalPrices(CartItemInterface $quoteItem)
    {
        /** @var \Magento\NegotiableQuote\Model\NegotiableQuoteItem $negotiableItem */
        $negotiableItem = $quoteItem->getExtensionAttributes()
            && $quoteItem->getExtensionAttributes()->getNegotiableQuoteItem()
            && $quoteItem->getExtensionAttributes()->getNegotiableQuoteItem()->getOriginalPrice()
            ? $quoteItem->getExtensionAttributes()->getNegotiableQuoteItem()
            : $this->negotiableQuoteItemFactory->create()->load($quoteItem->getItemId());
        $negotiableItem->setItemId($quoteItem->getItemId());
        if (!$negotiableItem->getOriginalPrice() && $quoteItem->getQty()) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $price = $quoteItem->getBasePrice();
            $taxAmount = $quoteItem->getBaseTaxAmount();
            $discountAmount = $this->getBaseTotalDiscountAmount($quoteItem);
            if ($quoteItem->getExtensionAttributes()
                && $quoteItem->getExtensionAttributes()->getNegotiableQuoteItem()) {
                $price = $quoteItem->getExtensionAttributes()->getNegotiableQuoteItem()->getOriginalPrice();
                $taxAmount = $quoteItem->getExtensionAttributes()
                    ->getNegotiableQuoteItem()
                    ->getOriginalTaxAmount();
                $discountAmount = $quoteItem->getExtensionAttributes()
                    ->getNegotiableQuoteItem()
                    ->getOriginalDiscountAmount();
            }
            $taxAmountPerItem = $taxAmount / $quoteItem->getQty();
            $discountAmountPerItem = $discountAmount / $quoteItem->getQty();
            $negotiableItem->setOriginalPrice($price)
                ->setOriginalTaxAmount($taxAmountPerItem)
                ->setOriginalDiscountAmount($discountAmountPerItem);
            $quoteItemExtension = $this->extensionFactory
                ->create(\Magento\Quote\Api\Data\CartItemInterface::class)
                ->setNegotiableQuoteItem($negotiableItem);
            $quoteItem->setExtensionAttributes($quoteItemExtension);
        }
    }

    /**
     * Calculate base total discount for quote item.
     *
     * @param CartItemInterface $quoteItem
     * @return int
     */
    private function getBaseTotalDiscountAmount(CartItemInterface $quoteItem)
    {
        $totalDiscountAmount = 0;
        $children = $quoteItem->getChildren();
        if (!empty($children) && $quoteItem->isChildrenCalculated()) {
            foreach ($children as $child) {
                $totalDiscountAmount += $child->getBaseDiscountAmount();
            }
        } else {
            $totalDiscountAmount = $quoteItem->getBaseDiscountAmount();
        }
        return $totalDiscountAmount;
    }
}
