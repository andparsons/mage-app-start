<?php

namespace Magento\NegotiableQuoteSharedCatalog\Plugin;

/**
 * Remove products from quotes if products were unassigned from shared catalog.
 */
class DeleteUnavailableQuoteItems
{
    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $config;

    /**
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement
     * @param \Magento\SharedCatalog\Api\StatusInfoInterface $config
     */
    public function __construct(
        \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement,
        \Magento\SharedCatalog\Api\StatusInfoInterface $config
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->config = $config;
    }

    /**
     * Remove product from quotes after unassigning product from shared catalog.
     *
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject
     * @param bool $result
     * @param \Magento\SharedCatalog\Api\Data\ProductItemInterface $item
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject,
        $result,
        \Magento\SharedCatalog\Api\Data\ProductItemInterface $item
    ) {
        if ($result) {
            $this->quoteManagement->deleteItems(
                [$item->getId()],
                $item->getCustomerGroupId(),
                $this->config->getActiveSharedCatalogStoreIds()
            );
        }

        return $result;
    }

    /**
     * Remove products from quotes after unassigning products from shared catalog.
     *
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject
     * @param bool $result
     * @param \Magento\SharedCatalog\Api\Data\ProductItemInterface[] $items
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteItems(
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject,
        $result,
        array $items
    ) {
        if ($result) {
            $productIdsByGroupId = [];
            foreach ($items as $productItem) {
                $productIdsByGroupId[$productItem->getCustomerGroupId()][] = $productItem->getId();
            }
            foreach ($productIdsByGroupId as $customerGroupId => $productIds) {
                $this->quoteManagement->deleteItems(
                    $productIds,
                    $customerGroupId,
                    $this->config->getActiveSharedCatalogStoreIds()
                );
            }
        }

        return $result;
    }
}
