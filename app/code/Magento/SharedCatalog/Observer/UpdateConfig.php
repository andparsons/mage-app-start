<?php
namespace Magento\SharedCatalog\Observer;

use Magento\Company\Api\StatusServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface as AppScopeInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SharedCatalog\Model\Config as SharedCatalogModuleConfig;
use Magento\SharedCatalog\Model\Permissions\Config as PermissionsConfig;
use Magento\SharedCatalog\Model\Permissions\Synchronizer;
use Magento\Store\Model\ScopeInterface;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface as ConfigResource;

/**
 * Additional actions after saving data to system config.
 */
class UpdateConfig implements ObserverInterface
{
    /**
     * @var StatusServiceInterface
     */
    private $companyStatusService;

    /**
     * @var SharedCatalogModuleConfig
     */
    private $sharedCatalogModuleConfig;

    /**
     * @var PermissionsConfig
     */
    private $permissionsConfig;

    /**
     * @var CatalogPermissionManagement
     */
    private $catalogPermissionsManagement;

    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var ScopeResolverPool
     */
    private $scopeResolverPool;

    /**
     * @var Synchronizer
     */
    private $permissionsSynchronizer;

    /**
     * @param StatusServiceInterface $companyStatusService
     * @param SharedCatalogModuleConfig $sharedCatalogModuleConfig
     * @param PermissionsConfig $permissionsConfig
     * @param CatalogPermissionManagement $catalogPermissionsManagement
     * @param ConfigResource $configResource
     * @param ScopeResolverPool $scopeResolverPool
     * @param Synchronizer $permissionsSynchronizer
     */
    public function __construct(
        StatusServiceInterface $companyStatusService,
        SharedCatalogModuleConfig $sharedCatalogModuleConfig,
        PermissionsConfig $permissionsConfig,
        CatalogPermissionManagement $catalogPermissionsManagement,
        ConfigResource $configResource,
        ScopeResolverPool $scopeResolverPool,
        Synchronizer $permissionsSynchronizer
    ) {
        $this->companyStatusService = $companyStatusService;
        $this->sharedCatalogModuleConfig = $sharedCatalogModuleConfig;
        $this->permissionsConfig = $permissionsConfig;
        $this->catalogPermissionsManagement = $catalogPermissionsManagement;
        $this->configResource = $configResource;
        $this->scopeResolverPool = $scopeResolverPool;
        $this->permissionsSynchronizer = $permissionsSynchronizer;
    }

    /**
     * Update permissions after updated config values.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $scope = $this->resolveScope($event);

        $changedPaths = (array) $event->getData('changed_paths');
        $isCompanyChanged = \in_array('btob/website_configuration/company_active', $changedPaths);
        $isSharedCatalogChanged = \in_array(SharedCatalogModuleConfig::CONFIG_SHARED_CATALOG, $changedPaths);

        $isCompanyActive = $this->companyStatusService->isActive(
            $scope->getScopeType(),
            $scope->getCode()
        );
        $isSharedCatalogActive = $this->sharedCatalogModuleConfig->isActive(
            $scope->getScopeType(),
            $scope->getCode()
        );

        if ($isCompanyChanged && !$isCompanyActive && $isSharedCatalogActive) {
            $this->configResource->saveConfig(
                SharedCatalogModuleConfig::CONFIG_SHARED_CATALOG,
                0,
                $scope->getScopeType(),
                $scope->getId()
            );

            $isSharedCatalogChanged = 1;
            $isSharedCatalogActive = 0;
        }
        if ($isSharedCatalogChanged) {
            $scopeId = $scope->getId() ? (int) $scope->getId() : null;
            if ($isSharedCatalogActive) {
                $this->permissionsConfig->enable($scopeId);
                $this->catalogPermissionsManagement->setPermissionsForAllCategories($scopeId);
            } else {
                $this->permissionsSynchronizer->removeCategoryPermissions($scopeId);
            }
        }
    }

    /**
     * Resolve scope from event.
     *
     * @param Event $event
     * @return AppScopeInterface
     */
    private function resolveScope(Event $event): AppScopeInterface
    {
        $scopeIdentifier = $event->getWebsite();
        $scopeType = $scopeIdentifier
            ? ScopeInterface::SCOPE_WEBSITES
            : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeResolver = $this->scopeResolverPool->get($scopeType);
        $scope = $scopeResolver->getScope($scopeIdentifier);

        return $scope;
    }
}
