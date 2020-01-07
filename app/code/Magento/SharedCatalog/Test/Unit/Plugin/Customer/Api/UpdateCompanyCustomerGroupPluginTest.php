<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\Customer\Api;

use \Magento\Company\Api\Data\CompanyInterface;

/**
 * Unit test for GroupRepositoryInterfacePlugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCompanyCustomerGroupPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManagement;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Plugin\Customer\Api\UpdateCompanyCustomerGroupPlugin
     */
    private $updateCompanyCustomerGroupPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->catalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->moduleConfig = $this->getMockBuilder(\Magento\SharedCatalog\Model\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->updateCompanyCustomerGroupPlugin = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\Customer\Api\UpdateCompanyCustomerGroupPlugin::class,
            [
                'companyRepository' => $this->companyRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'groupManagement' => $this->groupManagement,
                'companyManagement' => $this->companyManagement,
                'catalogManagement' => $this->catalogManagement,
                'storeManager' => $this->storeManager,
                'moduleConfig' => $this->moduleConfig
            ]
        );
    }

    /**
     * Test aroundDeleteById method.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAfterDeleteById()
    {
        $customerGroupId = 10;
        $publicGroupId = 11;
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')
            ->with(CompanyInterface::CUSTOMER_GROUP_ID, $customerGroupId)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteria);
        $this->companyRepository->expects($this->atLeastOnce())->method('getList')
            ->with($searchCriteria)->willReturn($searchResults);
        $searchResults->expects($this->atLeastOnce())->method('getItems')->willReturn(new \ArrayIterator([$company]));
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $publicCatalog = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $publicCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($publicGroupId);
        $this->catalogManagement->expects($this->atLeastOnce())->method('getPublicCatalog')->willReturn($publicCatalog);
        $company->expects($this->atLeastOnce())->method('setCustomerGroupId')->with($publicGroupId)->willReturnSelf();
        $this->companyRepository->expects($this->atLeastOnce())->method('save')->with($company)->willReturn($company);
        $this->companyManagement->expects($this->never())->method('getAdminByCompanyId');
        $this->groupManagement->expects($this->never())->method('getDefaultGroup');
        $groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->assertTrue(
            $this->updateCompanyCustomerGroupPlugin->afterDeleteById(
                $groupRepository,
                true,
                $customerGroupId
            )
        );
    }

    /**
     * Test aroundDeleteById method with exception.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAfterDeleteByIdWithException()
    {
        $companyId = 1;
        $storeId = 2;
        $customerGroupId = 10;
        $defaultGroupId = 11;
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $company->expects($this->atLeastOnce())->method('getId')->willReturn($companyId);
        $companyAdmin = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $companyAdmin->expects($this->atLeastOnce())->method('getStoreId')->willReturn($storeId);
        $defaultGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()->getMock();
        $defaultGroup->expects($this->atLeastOnce())->method('getId')->willReturn($defaultGroupId);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')
            ->with(CompanyInterface::CUSTOMER_GROUP_ID, $customerGroupId)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteria);
        $this->companyRepository->expects($this->atLeastOnce())->method('getList')
            ->with($searchCriteria)->willReturn($searchResults);
        $searchResults->expects($this->atLeastOnce())->method('getItems')->willReturn(new \ArrayIterator([$company]));
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $this->catalogManagement->expects($this->atLeastOnce())->method('getPublicCatalog')->willThrowException(
            new \Magento\Framework\Exception\NoSuchEntityException()
        );
        $this->companyManagement->expects($this->atLeastOnce())
            ->method('getAdminByCompanyId')->with($companyId)->willReturn($companyAdmin);
        $this->groupManagement->expects($this->atLeastOnce())
            ->method('getDefaultGroup')->with($storeId)->willReturn($defaultGroup);
        $company->expects($this->atLeastOnce())->method('setCustomerGroupId')->with($defaultGroupId)->willReturnSelf();
        $this->companyRepository->expects($this->atLeastOnce())->method('save')->with($company)->willReturn($company);
        $groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->assertTrue(
            $this->updateCompanyCustomerGroupPlugin->afterDeleteById(
                $groupRepository,
                true,
                $customerGroupId
            )
        );
    }
}
