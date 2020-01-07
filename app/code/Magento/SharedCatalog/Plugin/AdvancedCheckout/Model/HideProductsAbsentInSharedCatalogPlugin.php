<?php
declare(strict_types=1);
namespace Magento\SharedCatalog\Plugin\AdvancedCheckout\Model;

use Magento\SharedCatalog\Api\StatusInfoInterface;
use Magento\SharedCatalog\Model\Customer\AvailableProducts;
use Magento\AdvancedCheckout\Model\Cart;
use Magento\AdvancedCheckout\Helper\Data;
use Magento\Customer\Model\GroupManagement;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin for the AdvancedCheckout Cart model to change item status on not found.
 */
class HideProductsAbsentInSharedCatalogPlugin
{
    /**
     * @var StatusInfoInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AvailableProducts
     */
    private $availableProducts;

    /**
     * @param StatusInfoInterface $config
     * @param StoreManagerInterface $storeManager
     * @param AvailableProducts $availableProducts
     */
    public function __construct(
        StatusInfoInterface $config,
        StoreManagerInterface $storeManager,
        AvailableProducts $availableProducts
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->availableProducts = $availableProducts;
    }

    /**
     * Change item code to not found if appropriate product is not in the shared catalog.
     *
     * @param Cart $subject
     * @param array $item
     * @return array
     */
    public function afterCheckItem(Cart $subject, array $item): array
    {
        $website = $this->storeManager->getWebsite()->getId();
        if ($this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            $customer = $subject->getActualQuote()->getCustomer();
            $groupId = $customer && $customer->getId()
                ? (int) $customer->getGroupId()
                : GroupManagement::NOT_LOGGED_IN_ID;
            if (!$this->availableProducts->isProductAvailable($groupId, $item['sku'])) {
                $item['code'] = Data::ADD_ITEM_STATUS_FAILED_SKU;
            }
        }
        return $item;
    }
}
