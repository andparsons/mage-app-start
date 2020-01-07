<?php
namespace Magento\SharedCatalog\Plugin\Quote\Api;

use Magento\Store\Model\ScopeInterface;

/**
 * Denies to add product to cart if SharedCatalog module is active and product is not in a shared catalog.
 */
class ValidateAddProductToCartPlugin
{
    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $moduleConfig;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator
     */
    private $sharedCatalogLocator;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface
     */
    private $sharedCatalogProductManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\SharedCatalog\Api\StatusInfoInterface $moduleConfig
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
     * @param \Magento\SharedCatalog\Api\ProductManagementInterface $sharedCatalogProductManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\SharedCatalog\Api\StatusInfoInterface $moduleConfig,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement,
        \Magento\SharedCatalog\Api\ProductManagementInterface $sharedCatalogProductManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->quoteRepository = $quoteRepository;
        $this->sharedCatalogLocator = $sharedCatalogLocator;
        $this->sharedCatalogManagement = $sharedCatalogManagement;
        $this->sharedCatalogProductManagement = $sharedCatalogProductManagement;
        $this->storeManager = $storeManager;
    }

    /**
     * Denies to add product to cart if SharedCatalog module is active and product is not in a shared catalog.
     *
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Quote\Api\CartItemRepositoryInterface $subject,
        \Magento\Quote\Api\Data\CartItemInterface $cartItem
    ) {
        $website = $this->storeManager->getWebsite()->getId();
        if ($this->moduleConfig->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            $quote = $this->quoteRepository->get($cartItem->getQuoteId());
            $sharedCatalog = $this->getSharedCatalog($quote->getCustomerGroupId());
            $sku = $cartItem->getSku();

            if (!$sharedCatalog || !$this->isProductInSharedCatalog($sharedCatalog->getId(), $sku)) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Requested product doesn\'t exist: %1.', $sku)
                );
            }
        }

        return [$cartItem];
    }

    /**
     * Get shared catalog by customer group id if $customerGroupId is not empty or load public shared catalog.
     *
     * @param int $customerGroupId
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|null
     */
    private function getSharedCatalog($customerGroupId)
    {
        if ($customerGroupId) {
            return $this->sharedCatalogLocator->getSharedCatalogByCustomerGroup($customerGroupId);
        }

        return $this->sharedCatalogManagement->getPublicCatalog();
    }

    /**
     * Is shared catalog contain the product with given sku.
     *
     * @param int $sharedCatalogId
     * @param string $sku
     * @return bool
     */
    private function isProductInSharedCatalog($sharedCatalogId, $sku)
    {
        $productSkus = $this->sharedCatalogProductManagement->getProducts($sharedCatalogId);

        return in_array($sku, $productSkus);
    }
}
