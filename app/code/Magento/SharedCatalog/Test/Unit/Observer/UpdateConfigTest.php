<?php

namespace Magento\SharedCatalog\Test\Unit\Observer;

use Magento\Company\Api\StatusServiceInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\SharedCatalog\Model\Config as SharedCatalogConfig;
use Magento\SharedCatalog\Model\Permissions\Config as PermissionsConfig;
use Magento\SharedCatalog\Model\Permissions\Synchronizer;
use Magento\SharedCatalog\Observer\UpdateConfig;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UpdateConfigTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|StatusServiceInterface
     */
    protected $companyStatusService;

    /**
     * @var MockObject|SharedCatalogConfig
     */
    protected $sharedCatalogModuleConfig;

    /**
     * @var MockObject|PermissionsConfig
     */
    private $permissionsConfig;

    /**
     * @var MockObject|ConfigInterface
     */
    private $configResourceMock;

    /**
     * @var MockObject|Observer
     */
    protected $observer;

    /**
     * @var MockObject|Event
     */
    protected $event;

    /**
     * @var MockObject|UpdateConfig
     */
    protected $updateConfig;

    /**
     * @var MockObject|CatalogPermissionManagement
     */
    private $catalogPermissionsManagement;

    /**
     * @var MockObject|ScopeInterface
     */
    private $scope;

    /**
     * @var MockObject|Synchronizer
     */
    private $permissionsSynchronizer;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->companyStatusService = $this->createMock(StatusServiceInterface::class);
        $this->sharedCatalogModuleConfig = $this->createMock(SharedCatalogConfig::class);
        $this->permissionsConfig = $this->createMock(PermissionsConfig::class);
        $this->configResourceMock = $this->createMock(ConfigInterface::class);

        $this->observer = $this->createMock(Observer::class);
        $this->event = $this->getMockBuilder(Event::class)
            ->setMethods(['getWebsite', 'getData'])
            ->disableOriginalConstructor()->getMock();
        $this->observer->expects($this->any())->method('getEvent')
            ->willReturn($this->event);
        $this->catalogPermissionsManagement = $this->createMock(CatalogPermissionManagement::class);
        $this->permissionsSynchronizer = $this->createMock(Synchronizer::class);

        $scopeResolverPool = $this->createMock(ScopeResolverPool::class);
        $scopeResolver = $this->createMock(ScopeResolverInterface::class);
        $scopeResolverPool->method('get')
            ->willReturn($scopeResolver);
        $this->scope = $this->createMock(ScopeInterface::class);
        $scopeResolver->method('getScope')
            ->willReturn($this->scope);

        $objectManager = new ObjectManager($this);
        $this->updateConfig = $objectManager->getObject(
            UpdateConfig::class,
            [
                'companyStatusService' => $this->companyStatusService,
                'sharedCatalogModuleConfig' => $this->sharedCatalogModuleConfig,
                'permissionsConfig' => $this->permissionsConfig,
                'catalogPermissionsManagement' => $this->catalogPermissionsManagement,
                'configResource' => $this->configResourceMock,
                'scopeResolverPool' => $scopeResolverPool,
                'permissionsSynchronizer' => $this->permissionsSynchronizer,
            ]
        );
    }

    /**
     * @param int $eventWebsiteId
     * @param bool $isCompanyActive
     * @param bool $isSharedCatalogActive
     * @param int $enablingCalls
     * @param int $disablingCalls
     * @return void
     * @dataProvider dataProviderExecute
     */
    public function testExecute(
        int $eventWebsiteId,
        bool $isCompanyActive,
        bool $isSharedCatalogActive,
        int $enablingCalls,
        int $disablingCalls
    ) {
        $this->event->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->willReturn($eventWebsiteId);
        $this->event->expects($this->once())
            ->method('getData')
            ->with('changed_paths')
            ->willReturn(
                [
                    'btob/website_configuration/company_active',
                    SharedCatalogConfig::CONFIG_SHARED_CATALOG,
                ]
            );

        $this->scope->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventWebsiteId);

        $this->companyStatusService->expects($this->once())
            ->method('isActive')
            ->willReturn($isCompanyActive);

        $this->sharedCatalogModuleConfig->expects($this->any())
            ->method('isActive')
            ->willReturn($isSharedCatalogActive);

        $isRequireModuleDisable = !$isCompanyActive && $isSharedCatalogActive;
        $this->configResourceMock->expects(
            $this->exactly($isRequireModuleDisable ? 1 : 0)
        )->method('saveConfig');

        $this->catalogPermissionsManagement->expects($this->exactly($enablingCalls))
            ->method('setPermissionsForAllCategories');
        $this->permissionsConfig->expects($this->exactly($enablingCalls))
            ->method('enable');
        $this->permissionsSynchronizer->expects($this->exactly($disablingCalls))
            ->method('removeCategoryPermissions');

        $this->updateConfig->execute($this->observer);
    }

    /**
     * @return array
     */
    public function dataProviderExecute(): array
    {
        return [
            [1, true, true, 1, 0],
            [0, false, true, 0, 1],
            [1, false, false, 0, 1],
            [0, true, false, 0, 1],
        ];
    }
}
