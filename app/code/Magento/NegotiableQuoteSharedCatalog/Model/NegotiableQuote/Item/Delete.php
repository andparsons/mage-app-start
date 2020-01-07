<?php

namespace Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item;

/**
 * Remove products from negotiable quotes.
 */
class Delete
{
    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ItemRemove
     */
    private $itemRemove;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    private $quoteItemRepository;

    /**
     * @param \Magento\NegotiableQuote\Model\Quote\ItemRemove $itemRemove
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $quoteItemRepository
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Quote\ItemRemove $itemRemove,
        \Magento\Quote\Api\CartItemRepositoryInterface $quoteItemRepository
    ) {
        $this->itemRemove = $itemRemove;
        $this->quoteItemRepository = $quoteItemRepository;
    }

    /**
     * Delete quote items from negotiable quote and write history log.
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $quoteItems
     * @return void
     */
    public function deleteItems(array $quoteItems)
    {
        if ($quoteItems) {
            $quoteForDelete = [];
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quoteItems as $item) {
                $quoteId = $item->getOrigData(\Magento\Quote\Api\Data\CartItemInterface::KEY_QUOTE_ID);
                $quoteForDelete[$quoteId][$item->getProductId()][$item->getSku()] = $item->getItemId();
            }
            foreach ($quoteForDelete as $quoteId => $itemIdsByProductId) {
                foreach ($itemIdsByProductId as $productId => $itemIds) {
                    $this->deleteQuoteItem($quoteId, $itemIds, $productId, array_keys($itemIds));
                }
            }
        }
    }

    /**
     * Delete quote item, set notification and recalculate quote.
     *
     * @param int $quoteId
     * @param array $itemIds
     * @param int $productId
     * @param array $skus
     * @return void
     */
    private function deleteQuoteItem($quoteId, array $itemIds, $productId, array $skus)
    {
        foreach ($itemIds as $itemId) {
            $this->quoteItemRepository->deleteById($quoteId, $itemId);
        }
        $this->itemRemove->setNotificationRemove($quoteId, $productId, $skus);
    }
}
