<?php

namespace Magento\NegotiableQuoteSharedCatalog\Model;

/**
 * Delete products from quotes if the products are no longer available in the shared catalog.
 */
class QuoteManagement
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartInterfaceFactory
     */
    private $cartFactory;

    /**
     * @var array
     */
    private $quoteIds = [];

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository,
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Quote\Api\Data\CartInterfaceFactory $cartFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->cartFactory = $cartFactory;
    }

    /**
     * Delete products by IDs from existing quotes if products are unassigned from shared catalog.
     *
     * @param array $productIds
     * @param int $customerGroupId
     * @param array $stores [optional]
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be removed
     */
    public function deleteItems(array $productIds, $customerGroupId, array $stores = [])
    {
        $cartItems = $this->retrieveQuoteItems($customerGroupId, $productIds, $stores);
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($cartItems as $cartItem) {
            $this->cartItemRepository->deleteById(
                $cartItem->getOrigData(\Magento\Quote\Api\Data\CartItemInterface::KEY_QUOTE_ID),
                $cartItem->getItemId()
            );
        }
    }

    /**
     * Retrieve quote items by product ids for the customer group.
     *
     * @param int $customerGroupId
     * @param array $productIds
     * @param array $stores [optional]
     * @param bool $productsInclude [optional]
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function retrieveQuoteItems(
        $customerGroupId,
        array $productIds,
        array $stores = [],
        $productsInclude = true
    ) {
        if (!isset($this->quoteIds[$customerGroupId])) {
            $this->quoteIds[$customerGroupId] = [];
            $this->searchCriteriaBuilder->addFilter('customer_group_id', $customerGroupId);
            if ($stores) {
                $this->searchCriteriaBuilder->addFilter('store_id', $stores, 'in');
            }
            $quotes = $this->negotiableQuoteRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            foreach ($quotes as $quote) {
                $this->quoteIds[$customerGroupId][] = $quote->getId();
            }
        }
        $productCondition = $productsInclude ? 'in' : 'nin';

        return $this->retrieveQuoteItemsForQuotes($this->quoteIds[$customerGroupId], $productIds, $productCondition);
    }

    /**
     * Retrieve quote items by product ids for the customers.
     *
     * @param array $customerIds
     * @param array $productIds
     * @param array $stores
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function retrieveQuoteItemsForCustomers(
        array $customerIds,
        array $productIds,
        array $stores
    ) {
        $quoteIds = [];
        $this->searchCriteriaBuilder->addFilter('customer_id', $customerIds, 'in');
        if ($stores) {
            $this->searchCriteriaBuilder->addFilter('store_id', $stores, 'in');
        }
        $quotes = $this->negotiableQuoteRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($quotes as $quote) {
            $quoteIds[] = $quote->getId();
        }
        return $this->retrieveQuoteItemsForQuotes($quoteIds, $productIds, 'in');
    }

    /**
     * Retrieve quote items for quotes and products from arguments.
     *
     * @param array $quoteIds
     * @param array $productIds
     * @param string $productCondition
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    private function retrieveQuoteItemsForQuotes(array $quoteIds, array $productIds, $productCondition)
    {
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $itemCollection */
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('product_id', [$productCondition => $productIds]);
        $itemCollection->addFieldToFilter('quote_id', ['in' => $quoteIds]);
        $itemCollection->addFieldToFilter('parent_item_id', ['null' => true]);
        $quote = $this->cartFactory->create();
        //if collection don't have quote then getItems throw exception
        $itemCollection->setQuote($quote)->clear();
        return $itemCollection->getItems();
    }
}
