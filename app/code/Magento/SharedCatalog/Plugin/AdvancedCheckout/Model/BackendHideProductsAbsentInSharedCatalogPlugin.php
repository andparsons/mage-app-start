<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Plugin\AdvancedCheckout\Model;

use Magento\AdvancedCheckout\Helper\Data;
use Magento\AdvancedCheckout\Model\Cart;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Exception\LocalizedException;
use Magento\SharedCatalog\Api\StatusInfoInterface;
use Magento\SharedCatalog\Model\Customer\AvailableProducts;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin for AdvancedCheckout Cart model to change item status on not found.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class BackendHideProductsAbsentInSharedCatalogPlugin
{
    /**
     * @var StatusInfoInterface
     */
    private $config;

    /**
     * @var Quote
     */
    private $sessionQuote;

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
     * @param Quote $sessionQuote
     * @param StoreManagerInterface $storeManager
     * @param AvailableProducts $availableProducts
     */
    public function __construct(
        StatusInfoInterface $config,
        Quote $sessionQuote,
        StoreManagerInterface $storeManager,
        AvailableProducts $availableProducts
    ) {
        $this->config = $config;
        $this->sessionQuote = $sessionQuote;
        $this->storeManager = $storeManager;
        $this->availableProducts = $availableProducts;
    }

    /**
     * Change item code to not found if appropriate product is not in the shared catalog.
     *
     * @param Cart $subject
     * @param array $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterCheckItem(Cart $subject, array $item): array
    {
        $website = $this->storeManager->getWebsite()->getId();
        if ($this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            $quote = $this->sessionQuote->getQuote();
            $customer = $quote->getCustomer();
            $groupId = $customer && $customer->getId()
                ? (int) $customer->getGroupId()
                : (int) $quote->getCustomerGroupId();
            if (!$this->availableProducts->isProductAvailable($groupId, $item['sku'])) {
                $item['code'] = Data::ADD_ITEM_STATUS_FAILED_SKU;
            }
        }
        return $item;
    }
}
