<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model;

/**
 * Class QuoteRecalculate
 *
 * Class for quotes recalculation after quote item has been changed.
 */
class QuoteRecalculate
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item
     */
    private $itemResource;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ItemRemove
     */
    private $itemRemove;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $negotiableQuoteItemManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item $itemResource
     * @param \Magento\NegotiableQuote\Model\Quote\ItemRemove $itemRemove
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResource,
        \Magento\NegotiableQuote\Model\Quote\ItemRemove $itemRemove,
        \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->itemResource = $itemResource;
        $this->itemRemove = $itemRemove;
        $this->negotiableQuoteItemManagement = $negotiableQuoteItemManagement;
        $this->logger = $logger;
    }

    /**
     * Recalculate quotes after quote item changes.
     *
     * @param \Closure $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return mixed
     */
    public function updateQuotesByProduct(\Closure $proceed, \Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $connection = $this->itemResource->getConnection();
        $select = $connection->select()->reset();
        $select->from($this->itemResource->getMainTable(), ['sku', 'quote_id']);
        $select->where('product_id = ?', $product->getId());
        $items = $connection->fetchPairs($select);

        $quoteIds = [];
        foreach ($items as $quoteId => $sku) {
            $quoteIds[$quoteId][] = $sku;
        }

        $result = $proceed($product);

        foreach ($quoteIds as $quoteId => $skus) {
            try {
                $quote = $this->quoteRepository->get($quoteId, ['*']);
                if ($quote->getExtensionAttributes()
                    && $quote->getExtensionAttributes()->getNegotiableQuote()
                    && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
                ) {
                    $this->itemRemove->setNotificationRemove($quoteId, $product->getId(), $skus);
                    $this->negotiableQuoteItemManagement->updateQuoteItemsCustomPrices($quoteId);
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
        return $result;
    }
}
