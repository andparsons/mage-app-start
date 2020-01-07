<?php

namespace Magento\Company\Test\Unit\Plugin\Customer\Api;

use \Magento\Company\Api\Data\CompanyInterface;

/**
 * Unit test for ReassignCompaniesToDefaultGroup plugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReassignCompaniesToDefaultGroupTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Company\Plugin\Customer\Api\ReassignCompaniesToDefaultGroup
     */
    private $groupRepositoryPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );
        $this->searchCriteriaBuilder = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->groupManagement = $this->createMock(
            \Magento\Customer\Api\GroupManagementInterface::class
        );
        $this->companyManagement = $this->createMock(
            \Magento\Company\Api\CompanyManagementInterface::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->groupRepositoryPlugin = $objectManager->getObject(
            \Magento\Company\Plugin\Customer\Api\ReassignCompaniesToDefaultGroup::class,
            [
                'companyRepository' => $this->companyRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'groupManagement' => $this->groupManagement,
                'companyManagement' => $this->companyManagement,
            ]
        );
    }

    /**
     * Test aroundDeleteByUd method.
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAroundDeleteById()
    {
        $companyId = 1;
        $storeId = 2;
        $customerGroupId = 10;
        $defaultGroupId = 11;
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $companyAdmin = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $companyAdmin->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $defaultGroup = $this->createMock(\Magento\Customer\Api\Data\GroupInterface::class);
        $defaultGroup->expects($this->once())->method('getId')->willReturn($defaultGroupId);
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->with(CompanyInterface::CUSTOMER_GROUP_ID, $customerGroupId)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->companyRepository->expects($this->once())->method('getList')
            ->with($searchCriteria)->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getItems')->willReturn(new \ArrayIterator([$company]));
        $this->companyManagement->expects($this->once())
            ->method('getAdminByCompanyId')->with($companyId)->willReturn($companyAdmin);
        $this->groupManagement->expects($this->once())
            ->method('getDefaultGroup')->with($storeId)->willReturn($defaultGroup);
        $company->expects($this->once())->method('setCustomerGroupId')->with($defaultGroupId)->willReturnSelf();
        $this->companyRepository->expects($this->once())->method('save')->with($company)->willReturn($company);
        $groupRepository = $this->createMock(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $this->assertTrue(
            $this->groupRepositoryPlugin->aroundDeleteById(
                $groupRepository,
                function ($groupId) {
                    return true;
                },
                $customerGroupId
            )
        );
    }
}
