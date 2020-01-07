<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model;

use Magento\Quote\Model\Quote;
use Magento\NegotiableQuote\Model\Quote\ItemRemove;

/**
 * Class QuotePlugin
 */
class QuotePlugin
{
    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ItemRemove
     */
    private $itemRemove;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Construct
     *
     * @param ItemRemove $itemRemove
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        ItemRemove $itemRemove,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->itemRemove = $itemRemove;
        $this->serializer = $serializer;
    }

    /**
     * Check selected address for quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Closure $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface $subject
     * @return \Magento\Quote\Model\Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAssignCustomer(
        \Magento\Quote\Model\Quote $quote,
        \Closure $proceed,
        \Magento\Customer\Api\Data\CustomerInterface $subject
    ) {
        $billingAddress = $quote->getBillingAddress()->getCustomerAddressId() ? $quote->getBillingAddress() : null;
        $this->setNegotiableQuoteShippingAddressIfAbsent($quote);
        $shippingAddress = $quote->getShippingAddress()->getCustomerAddressId() ? $quote->getShippingAddress() : null;

        return $quote->assignCustomerWithAddressChange($subject, $billingAddress, $shippingAddress);
    }

    /**
     * Set Negotiable Quote shipping address if it was deleted for customer but presents in Negotiable Quote snapshot.
     *
     * @param Quote $quote
     * @return void
     */
    private function setNegotiableQuoteShippingAddressIfAbsent(\Magento\Quote\Model\Quote $quote)
    {
        if ($quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
            && !$quote->getShippingAddress()->getCustomerAddressId()
        ) {
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            try {
                $snapshot = $this->serializer->unserialize($negotiableQuote->getSnapshot());
            } catch (\InvalidArgumentException $e) {
                $snapshot = [];
            }
            $shapshotAddressId = $snapshot['shipping_address']['customer_address_id'] ?? null;
            if ($snapshot && $shapshotAddressId) {
                $quote->getShippingAddress()->setCustomerAddressId($shapshotAddressId);
            }
        }
    }

    /**
     * Sets "remove" notifications for items removed from a quote.
     *
     * If a quote has the `trigger_recollect` flag, its items have changed and they must be recollected.
     * After collectTotals() executes, items marked as deleted will be deleted. This method compares the items
     * that existed in the quote before collectTotals() was called to those remaining in the quote.
     * If there are any "before" items that do not exist in the "after" items, this method sets "remove" notifications.
     *
     * @param Quote $subject
     * @param \Closure $proceed
     * @return Quote
     */
    public function aroundCollectTotals(Quote $subject, \Closure $proceed)
    {
        if (!$subject->getData('trigger_recollect')) {
            $result = $proceed();
        } else {
            $itemsArray = $this->collectItemsData($subject);
            $result = $proceed();
            if ($itemsDiff = $this->diffItems($result, $itemsArray)) {
                foreach ($itemsDiff as $productId => $skus) {
                    $skus = array_unique($skus);
                    try {
                        $this->itemRemove->setNotificationRemove($result->getId(), $productId, $skus);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        // according to collectTotals() method definition it should not produce exceptions.
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Collect items data
     *
     * @param Quote $quote
     * @return array
     */
    private function collectItemsData(Quote $quote)
    {
        $itemsArray = [];
        foreach ($quote->getItemsCollection() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($item->getItemId()) {
                $itemsArray[$item->getItemId()] = ['productId' => $item->getProductId(), 'sku' => $item->getSku()];
            }
        }
        return $itemsArray;
    }

    /**
     * Diff items data
     *
     * @param Quote $quote
     * @param array $itemsArray
     * @return array
     */
    private function diffItems(Quote $quote, array $itemsArray)
    {
        $productsRemoved = [];
        foreach ($itemsArray as $itemId => $item) {
            $quoteItem = $quote->getItemById($itemId);
            if (!$quoteItem || $quoteItem->isDeleted()) {
                if (!isset($productsRemoved[$item['productId']])) {
                    $productsRemoved[$item['productId']] = [];
                }
                $productsRemoved[$item['productId']][] = $item['sku'];
            }
        }
        return $productsRemoved;
    }
}
