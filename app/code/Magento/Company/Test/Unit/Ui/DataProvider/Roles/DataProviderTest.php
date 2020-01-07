<?php

namespace Magento\Company\Test\Unit\Ui\DataProvider\Roles;

/**
 * Class DataProviderTest.
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
     * @var \Magento\Company\Model\RoleRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Model\UserRoleManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRoleManagement;

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
        $this->roleRepository = $this->createPartialMock(
            \Magento\Company\Model\RoleRepository::class,
            ['getList']
        );
        $this->userRoleManagement = $this->createPartialMock(
            \Magento\Company\Model\UserRoleManagement::class,
            ['getUsersCountByRoleId']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\Company\Ui\DataProvider\Roles\DataProvider::class,
            [
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'companyUser' => $this->companyUser,
                'roleRepository' => $this->roleRepository,
                'userRoleManagement' => $this->userRoleManagement,
            ]
        );
    }

    /**
     * Test getData method.
     *
     * @param int $totalRecords
     * @param array $data
     * @param int $usersCount
     * @param array $expectedResult
     * @return void
     * @dataProvider getDataDataProvider
     */
    public function testGetData($totalRecords, array $data, $usersCount, array $expectedResult)
    {
        $currentCompanyId = 1;
        $filter = $this->getMockForAbstractClass(
            \Magento\Framework\Api\Filter::class,
            [],
            '',
            false
        );
        $searchCriteria = $this->createPartialMock(
            \Magento\Framework\Api\Search\SearchCriteria::class,
            ['setRequestName']
        );
        $searchResult = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\RoleSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems', 'getTotalCount']
        );
        $item = $this->getMockForAbstractClass(
            \Magento\Framework\Api\ExtensibleDataInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getData', 'getRoleId']
        );
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addSortOrder')
            ->with('role_name', 'ASC')
            ->willReturnSelf();
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($currentCompanyId);
        $this->filterBuilder->expects($this->once())
            ->method('setField')
            ->with('main_table.company_id')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->with($currentCompanyId)->willReturnSelf();
        $this->filterBuilder->expects($this->any())->method('create')->willReturn($filter);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with($filter)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchCriteria->expects($this->once())->method('setRequestName')->willReturnSelf();
        $this->roleRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria, true)
            ->willReturn($searchResult);
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn($totalRecords);
        $searchResult->expects($this->once())->method('getItems')->willReturn([$item]);
        $item->expects($this->once())->method('getData')->willReturn($data);
        $item->expects($this->once())->method('getRoleId')->willReturn(1);

        $this->userRoleManagement->expects($this->once())
            ->method('getUsersCountByRoleId')
            ->with(1)
            ->willReturn($usersCount);

        $this->assertEquals($expectedResult, $this->dataProvider->getData());
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
                ['some_key' => 'some_value'],
                3,
                [
                    'totalRecords' => 1,
                    'items' => [
                        [
                            'some_key' => 'some_value',
                            'users_count' => 3
                        ]
                    ]
                ],
            ],
            [
                4,
                ['some_key2' => 'some_value2'],
                15,
                [
                    'totalRecords' => 4,
                    'items' => [
                        [
                            'some_key2' => 'some_value2',
                            'users_count' => 15
                        ]
                    ]
                ],
            ],
        ];
    }
}
