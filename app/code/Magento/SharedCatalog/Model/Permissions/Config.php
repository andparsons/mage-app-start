<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Model\Permissions;

use Magento\CatalogPermissions\App\ConfigInterface as CatalogPermissionsConfig;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Enables category permissions in system configuration when SharedCatalog is enabled.
 */
class Config
{
    /**
     * @var ReinitableConfigInterface
     */
    private $config;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @param ReinitableConfigInterface $config
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        ReinitableConfigInterface $config,
        ConfigFactory $configFactory
    ) {
        $this->config = $config;
        $this->configFactory = $configFactory;
    }

    /**
     * Enables category permissions.
     *
     * @param int|null $websiteId
     * @return void
     */
    public function enable(?int $websiteId): void
    {
        $this->enableCategoryPermissions();
        if ($websiteId) {
            $this->grantScopePermissions((int) $websiteId);
        }
        $this->config->reinit();
    }

    /**
     * Switch on category permissions.
     *
     * @return void
     */
    private function enableCategoryPermissions(): void
    {
        $config = $this->configFactory->create();
        $config->setDataByPath(CatalogPermissionsConfig::XML_PATH_ENABLED, 1);
        $config->setDataByPath(
            CatalogPermissionsConfig::XML_PATH_GRANT_CATALOG_CATEGORY_VIEW,
            CatalogPermissionsConfig::GRANT_ALL
        );
        $config->setDataByPath(
            CatalogPermissionsConfig::XML_PATH_GRANT_CATALOG_PRODUCT_PRICE,
            CatalogPermissionsConfig::GRANT_ALL
        );
        $config->setDataByPath(
            CatalogPermissionsConfig::XML_PATH_GRANT_CHECKOUT_ITEMS,
            CatalogPermissionsConfig::GRANT_ALL
        );
        $config->save();
    }

    /**
     * Grant all permissions for scope.
     *
     * @param int $websiteId
     * @return void
     */
    private function grantScopePermissions(int $websiteId): void
    {
        $scope = [
            'scope' => ScopeInterface::SCOPE_WEBSITES,
            'scope_id' => $websiteId,
        ];
        $config = $this->configFactory->create(['data' => $scope]);
        $config->setDataByPath(
            CatalogPermissionsConfig::XML_PATH_GRANT_CATALOG_CATEGORY_VIEW,
            CatalogPermissionsConfig::GRANT_ALL
        );
        $config->setDataByPath(
            CatalogPermissionsConfig::XML_PATH_GRANT_CATALOG_PRODUCT_PRICE,
            CatalogPermissionsConfig::GRANT_ALL
        );
        $config->setDataByPath(
            CatalogPermissionsConfig::XML_PATH_GRANT_CHECKOUT_ITEMS,
            CatalogPermissionsConfig::GRANT_ALL
        );
        $config->save();
    }

    /**
     * Retrieve config value for category access permission.
     *
     * @param int $customerGroupId
     * @param int|null $websiteId
     * @return bool
     */
    public function isAllowedCategoryView(int $customerGroupId, ?int $websiteId): bool
    {
        return $this->isAllowed(
            CatalogPermissionsConfig::XML_PATH_GRANT_CATALOG_CATEGORY_VIEW,
            $customerGroupId,
            $websiteId
        );
    }

    /**
     * Retrieve config value for product price permission.
     *
     * @param int $customerGroupId
     * @param int|null $websiteId
     * @return bool
     */
    public function isAllowedProductPrice(int $customerGroupId, ?int $websiteId): bool
    {
        return $this->isAllowed(
            CatalogPermissionsConfig::XML_PATH_GRANT_CATALOG_PRODUCT_PRICE,
            $customerGroupId,
            $websiteId
        );
    }

    /**
     * Retrieve config value for checkout items permission.
     *
     * @param int $customerGroupId
     * @param int|null $websiteId
     * @return bool
     */
    public function isAllowedCheckoutItems(int $customerGroupId, ?int $websiteId): bool
    {
        return $this->isAllowed(
            CatalogPermissionsConfig::XML_PATH_GRANT_CHECKOUT_ITEMS,
            $customerGroupId,
            $websiteId
        );
    }

    /**
     * Retrieve is action allowed from configuration.
     *
     * @param string $configPath
     * @param int $customerGroupId
     * @param int|null $websiteId
     * @return bool
     */
    private function isAllowed(string $configPath, int $customerGroupId, ?int $websiteId): bool
    {
        $scopeType = $websiteId ? ScopeInterface::SCOPE_WEBSITES : ReinitableConfigInterface::SCOPE_TYPE_DEFAULT;
        $mode = (int) $this->config->getValue($configPath, $scopeType, $websiteId);

        if (CatalogPermissionsConfig::GRANT_CUSTOMER_GROUP === $mode) {
            $allowedGroups = (array) $this->config->getValue($configPath . '_groups', $scopeType, $websiteId);
            $mode = in_array($customerGroupId, $allowedGroups)
                ? CatalogPermissionsConfig::GRANT_ALL
                : CatalogPermissionsConfig::GRANT_NONE;
        }

        return CatalogPermissionsConfig::GRANT_ALL === $mode;
    }
}
