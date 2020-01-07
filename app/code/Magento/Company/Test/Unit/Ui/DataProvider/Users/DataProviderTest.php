<?php

namespace Magento\Company\Test\Unit\Ui\DataProvider\Users;

/**
 * Class DataProviderTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Model\CompanyUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUser;

    /**
     * @var \Magento\Framework\Api\Search\ReportingInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reporting;

    /**
     * @var \Magento\Company\Model\CompanyAdminPermission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAdminPermission;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagement;

    /**
     * @var \Magento\Company\Model\Company\StructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureFactory;

    /**
     * @var \Magento\Company\Ui\DataProvider\Roles\DataProvider
     */
    private $dataProvider;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->filterBuilder = $this->createPartialMock(
            \Magento\Framework\Api\FilterBuilder::class,
            ['setField', 'setConditionType', 'setValue', 'create']
        );
        $this->searchCriteriaBuilder = $this->createPartialMock(
            \Magento\Framework\Api\Search\SearchCriteriaBuilder::class,
            ['addSortOrder', 'addFilter', 'create']
        );
        $this->companyUser = $this->createPartialMock(
            \Magento\Company\Model\CompanyUser::class,
            ['getCurrentCompanyId']
        );
        $this->reporting = $this->getMockForAbstractClass(
            \Magento\Framework\Api\Search\ReportingInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['search']
        );
        $this->companyAdminPermission = $this->createPartialMock(
            \Magento\Company\Model\CompanyAdminPermission::class,
            ['isGivenUserCompanyAdmin']
        );
        $this->roleManagement = $this->getMockForAbstractClass(
            \Magento\Company\Api\RoleManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCompanyAdminRoleId', 'getCompanyAdminRoleName']
        );
        $this->structureFactory = $this->createPartialMock(
            \Magento\Company\Model\Company\StructureFactory::class,
            ['create']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\Company\Ui\DataProvider\Users\DataProvider::class,
            [
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'companyUser' => $this->companyUser,
                'companyAdminPermission' => $this->companyAdminPermission,
                'roleManagement' => $this->roleManagement,
                'structureFactory' => $this->structureFactory,
                'reporting' => $this->reporting,
            ]
        );
    }

    /**
     * Test getData method.
     *
     * @param int $companyAdminRoleId
     * @param string $companyAdminRoleName
     * @param string $teamName
     * @param int $totalRecords
     * @param array $expectedResult
     * @return void
     * @dataProvider getDataDataProvider
     */
    public function testGetData(
        $companyAdminRoleId,
        $companyAdminRoleName,
        $teamName,
        $totalRecords,
        array $expectedResult
    ) {
        $companyId = 1;
        $customerId = 1;
        $filter = $this->getMockForAbstractClass(
            \Magento\Framework\Api\Filter::class,
            [],
            '',
            false
        );
        $searchResult = $this->getMockForAbstractClass(
            \Magento\Framework\Api\Search\SearchResultInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems', 'getTotalCount']
        );
        $item = $this->getMockForAbstractClass(
            \Magento\Framework\Api\Search\DocumentInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getId', 'getCustomAttributes']
        );
        $customAttribute = $this->getMockForAbstractClass(
            \Magento\Framework\Api\AttributeInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getAttributeCode', 'getValue']
        );
        $structure = $this->createPartialMock(
            \Magento\Company\Model\Company\Structure::class,
            ['getTeamNameByCustomerId']
        );
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($companyId);
        $this->filterBuilder->expects($this->once())
            ->method('setField')
            ->with('company_customer.company_id')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())
            ->method('setValue')
            ->with($companyId)
            ->willReturnSelf();
        $this->filterBuilder->expects($this->any())->method('create')->willReturn($filter);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with($filter)
            ->willReturnSelf();
        $searchCriteria = $this->createPartialMock(
            \Magento\Framework\Api\Search\SearchCriteria::class,
            ['setRequestName']
        );
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchCriteria->expects($this->once())->method('setRequestName')->willReturnSelf();
        $this->reporting->expects($this->once())->method('search')->with($searchCriteria)->willReturn($searchResult);
        $searchResult->expects($this->once())->method('getItems')->willReturn([$item]);
        $item->expects($this->once())->method('getCustomAttributes')->willReturn([$customAttribute]);
        $customAttribute->expects($this->once())->method('getAttributeCode')->willReturn('some_code');
        $customAttribute->expects($this->once())->method('getValue')->willReturn('some_value');
        $this->companyAdminPermission->expects($this->once())
            ->method('isGivenUserCompanyAdmin')
            ->with(1)
            ->willReturn(true);
        $this->roleManagement->expects($this->once())->method('getCompanyAdminRoleId')->willReturn($companyAdminRoleId);
        $this->roleManagement->expects($this->once())
            ->method('getCompanyAdminRoleName')
            ->willReturn($companyAdminRoleName);
        $item->expects($this->exactly(2))->method('getId')->willReturn($customerId);
        $this->structureFactory->expects($this->once())->method('create')->willReturn($structure);
        $structure->expects($this->once())->method('getTeamNameByCustomerId')->with($customerId)->willReturn($teamName);
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn($totalRecords);

        $this->assertEquals($expectedResult, $this->dataProvider->getData());
    }

    /**
     * Test getData method throws exception.
     *
     * @return void
     */
    public function testGetDataWithException()
    {
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn(0);
        $this->filterBuilder->expects($this->once())
            ->method('setField')
            ->with('company_customer.company_id')
            ->willReturnSelf();

        $this->assertEquals(
            [
                'items' => [],
                'totalRecords' => 0,
            ],
            $this->dataProvider->getData()
        );
    }

    /**
     * Data provider for getData method.
     *
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            [
                1,
                'Role Name',
                'Team Name',
                1,
                [
                    'totalRecords' => 1,
                    'items' => [
                        [
                            'role_id' => 1,
                            'role_name' => new \Magento\Framework\Phrase('Role Name'),
                            'team' => 'Team Name',
                            'some_code' => 'some_value'
                        ]
                    ]
                ],
            ],
            [
                15,
                'Role Name 15',
                'Custom Team Name',
                3,
                [
                    'totalRecords' => 3,
                    'items' => [
                        [
                            'role_id' => 15,
                            'role_name' => new \Magento\Framework\Phrase('Role Name 15'),
                            'team' => 'Custom Team Name',
                            'some_code' => 'some_value'
                        ]
                    ]
                ],
            ],
        ];
    }
}
