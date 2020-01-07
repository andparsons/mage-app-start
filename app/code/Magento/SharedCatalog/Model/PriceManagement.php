<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\PriceManagementInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Store\Model\Store;

/**
 * Shared catalog prices actions.
 */
class PriceManagement implements PriceManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface
     */
    private $sharedCatalogProductItemManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * PriceManagement constructor.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\SharedCatalog\Api\ProductItemManagementInterface $productItemManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\SharedCatalog\Api\ProductItemManagementInterface $productItemManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->sharedCatalogProductItemManagement = $productItemManagement;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function saveProductTierPrices(SharedCatalogInterface $sharedCatalog, array $prices)
    {
        $this->enableDefaultStore();

        foreach ($prices as $productId => $priceData) {
            $product  = $this->productRepository->getById($productId, false, Store::DEFAULT_STORE_ID, true);
            $this->sharedCatalogProductItemManagement->updateTierPrices($sharedCatalog, $product, $priceData);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteProductTierPrices(SharedCatalogInterface $sharedCatalog, array $skus)
    {
        $this->enableDefaultStore();

        $this->sharedCatalogProductItemManagement->deleteTierPricesBySku($sharedCatalog, $skus);
        return $this;
    }

    /**
     * Enable default store.
     *
     * @return void
     */
    private function enableDefaultStore()
    {
        $store = $this->storeManager->getStore(Store::DEFAULT_STORE_ID);
        $this->storeManager->setCurrentStore($store->getCode());
    }
}
