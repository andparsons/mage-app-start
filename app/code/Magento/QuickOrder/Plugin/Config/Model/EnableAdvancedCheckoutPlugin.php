<?php
namespace Magento\QuickOrder\Plugin\Config\Model;

/**
 * Enable AdvancedCheckout extension if QuickOrder enabled.
 */
class EnableAdvancedCheckoutPlugin
{
    /**
     * @var \Magento\QuickOrder\Model\Config
     */
    private $quickOrderConfig;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $configResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $configScope;

    /**
     * @var string
     */
    private $advancedCheckoutConfigPath = 'sales/product_sku/my_account_enable';

    /**
     * @param \Magento\QuickOrder\Model\Config $quickOrderConfig
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\QuickOrder\Model\Config $quickOrderConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->quickOrderConfig = $quickOrderConfig;
        $this->configResource = $configResource;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve configuration scope and save it into internal cache.
     * If the scope was already retrieved get it from the cache.
     *
     * @param \Magento\Config\Model\Config $config
     * @return array
     */
    private function retrieveConfigScope(\Magento\Config\Model\Config $config)
    {
        if (!isset($this->configScope)) {
            $this->configScope = [];
            $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeCode = null;
            $scopeId = 0;

            if ($config->getStore()) {
                $scope = 'stores';
                $store = $this->storeManager->getStore($config->getStore());
                $scopeCode = (string)$store->getCode();
                $scopeId = (int)$store->getId();
            } elseif ($config->getWebsite()) {
                $scope = 'websites';
                $website = $this->storeManager->getWebsite($config->getWebsite());
                $scopeCode = (string)$website->getCode();
                $scopeId = (int)$website->getId();
            }
            $this->configScope['scope'] = $scope;
            $this->configScope['scopeCode'] = $scopeCode;
            $this->configScope['scopeId'] = $scopeId;
        }

        return $this->configScope;
    }

    /**
     * Enable AdvancedCheckout extension if QuickOrder enabled.
     *
     * @param \Magento\Config\Model\Config $subject
     * @param \Closure $proceed
     *
     * @return \Magento\Config\Model\Config
     */
    public function aroundSave(\Magento\Config\Model\Config $subject, \Closure $proceed)
    {
        $configScope = $this->retrieveConfigScope($subject);
        $isQuickOrderActiveBefore = $this->quickOrderConfig->isActive($configScope['scope'], $configScope['scopeCode']);
        $result = $proceed();

        if ($isQuickOrderActiveBefore === false
            && $this->quickOrderConfig->isActive($configScope['scope'], $configScope['scopeCode']) === true
        ) {
            $this->enableAdvancedCheckout($subject);
        }

        return $result;
    }

    /**
     * Enable AdvancedCheckout extension.
     *
     * @param \Magento\Config\Model\Config $config
     * @return void
     */
    private function enableAdvancedCheckout(\Magento\Config\Model\Config $config)
    {
        $configScope = $this->retrieveConfigScope($config);
        $this->configResource->saveConfig(
            $this->advancedCheckoutConfigPath,
            \Magento\AdvancedCheckout\Model\Cart\Sku\Source\Settings::YES_VALUE,
            $configScope['scope'],
            $configScope['scopeId']
        );

        if ($configScope['scope'] === \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $this->deleteWebsiteConfigs($this->advancedCheckoutConfigPath);
        }
    }

    /**
     * Delete system config values with websites scope.
     *
     * @param string $path
     * @return void
     */
    private function deleteWebsiteConfigs($path)
    {
        $websiteIds = $this->getAllWebsiteIds();

        foreach ($websiteIds as $websiteId) {
            $this->configResource->deleteConfig(
                $path,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );
        }
    }

    /**
     * Get list of all websites IDs.
     *
     * @return array
     */
    private function getAllWebsiteIds()
    {
        $websiteIds = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }
}
