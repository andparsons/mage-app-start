<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Test for class UserRoleManagement.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserRoleManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\UserRole\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRoleCollectionFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Role\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleCollectionFactory;

    /**
     * @var \Magento\Company\Model\UserRoleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRoleFactory;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagement;

    /**
     * @var \Magento\Company\Model\CompanyAdminPermission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAdminPermission;

    /**
     * @var \Magento\Company\Model\UserRoleManagement
     */
    private $userRoleManagement;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userRoleCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\UserRole\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->roleCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Role\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->userRoleFactory = $this->getMockBuilder(\Magento\Company\Model\UserRoleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->roleManagement = $this->getMockBuilder(\Magento\Company\Api\RoleManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyDefaultRole', 'getAdminRole'])
            ->getMockForAbstractClass();
        $this->companyAdminPermission = $this->getMockBuilder(\Magento\Company\Model\CompanyAdminPermission::class)
            ->disableOriginalConstructor()
            ->setMethods(['isGivenUserCompanyAdmin'])
            ->getMock();
        $this->aclDataCacheMock = $this->getMockBuilder(\Magento\Framework\Acl\Data\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->roleRepository = $this->getMockBuilder(\Magento\Company\Api\RoleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'getItems'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->userRoleManagement = $objectManager->getObject(
            \Magento\Company\Model\UserRoleManagement::class,
            [
                'userRoleCollectionFactory' => $this->userRoleCollectionFactory,
                'roleCollectionFactory' => $this->roleCollectionFactory,
                'userRoleFactory' => $this->userRoleFactory,
                'customerRepository' => $this->customerRepository,
                'roleManagement' => $this->roleManagement,
                'companyAdminPermission' => $this->companyAdminPermission,
                'aclDataCache' => $this->aclDataCacheMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder
            ]
        );
    }

    /**
     * Test assignUserDefaultRole method.
     *
     * @return void
     */
    public function testAssignUserDefaultRole()
    {
        $userId = 1;
        $companyId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $userRoleCollection = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\UserRole\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'load', 'getItems'])
            ->getMock();
        $userRole = $this->getMockBuilder(\Magento\Company\Model\UserRole::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();
        $roleModel = $this->getMockBuilder(\Magento\Company\Model\Role::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRoleId', 'setUserId', 'save'])
            ->getMock();
        $this->roleManagement->expects($this->once())
            ->method('getCompanyDefaultRole')
            ->with($companyId)
            ->willReturn($role);
        $this->roleManagement->expects($this->once())
            ->method('getRolesByCompanyId')
            ->with($companyId)
            ->willReturn([$role]);
        $this->userRoleCollectionFactory->expects($this->once())->method('create')->willReturn($userRoleCollection);
        $userRoleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('user_id', ['eq' => $userId])
            ->willReturnSelf();
        $userRoleCollection->expects($this->once())->method('load')->willReturnSelf();
        $userRoleCollection->expects($this->once())->method('getItems')->willReturn([$userRole]);
        $userRole->expects($this->once())->method('delete')->willReturn(true);
        $this->userRoleFactory->expects($this->once())->method('create')->willReturn($roleModel);
        $role->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $roleModel->expects($this->once())->method('setRoleId')->with(1)->willReturnSelf();
        $roleModel->expects($this->once())->method('setUserId')->with($userId)->willReturnSelf();
        $roleModel->expects($this->once())->method('save')->willReturnSelf();
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'getCompanyId'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customer);
        $this->aclDataCacheMock->expects($this->once())
            ->method('clean');

        $this->userRoleManagement->assignUserDefaultRole($userId, $companyId);
    }

    /**
     * Test assignUserDefaultRole method with not exists company.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage You cannot update the requested attribute. Row ID: roleId = 1.
     */
    public function testAssignUserDefaultRoleWithEmptyCompanyException()
    {
        $userId = 1;
        $companyId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->roleManagement->expects($this->once())
            ->method('getCompanyDefaultRole')
            ->with($companyId)
            ->willReturn($role);

        $role->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'getCompanyId'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyId')
            ->willReturn(0);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customer);

        $this->userRoleManagement->assignUserDefaultRole($userId, $companyId);
    }

    /**
     * Test assignUserDefaultRole method with not assigned role.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "id" is required. Enter and try again.
     */
    public function testAssignUserDefaultRoleWithEmptyRoleException()
    {
        $userId = 1;
        $companyId = 1;
        $this->roleManagement->expects($this->once())
            ->method('getCompanyDefaultRole')
            ->with($companyId)
            ->willReturn(null);
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'getCompanyId'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customer);

        $this->userRoleManagement->assignUserDefaultRole($userId, $companyId);
    }

    /**
     * Test assignUserDefaultRole method with exception of user is another company admin.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage You cannot assign a different role to a company admin.
     */
    public function testAssignUserDefaultRoleWithAnotherCompanyAdmin()
    {
        $userId = 1;
        $companyId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->roleManagement->expects($this->once())
            ->method('getCompanyDefaultRole')
            ->with($companyId)
            ->willReturn($role);
        $role->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->companyAdminPermission
            ->expects($this->once())
            ->method('isGivenUserCompanyAdmin')
            ->with($userId)
            ->willReturn(true);
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'getCompanyId'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customer);

        $this->userRoleManagement->assignUserDefaultRole($userId, $companyId);
    }

    /**
     * Test assignRoles method with multiple assigned roles to company.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage You cannot assign multiple roles to a user.
     */
    public function testAssignRolesWithMultipleRolesAssigned()
    {
        $userId = 1;
        $companyId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $role->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturnOnConsecutiveCalls(1, 1, 3, 5);
        $this->roleManagement->expects($this->once())
            ->method('getRolesByCompanyId')
            ->with($companyId)
            ->willReturn([$role, $role]);

        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'getCompanyId'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customer);

        $this->userRoleManagement->assignRoles($userId, [$role, $role, $role]);
    }

    /**
     * Test assignRoles method with assign of role that is not exist in company.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "1" provided for the role_id field.
     */
    public function testAssignRolesWithAssignAbsendRole()
    {
        $userId = 1;
        $companyId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $role->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $userRole = $this->getMockBuilder(\Magento\Company\Model\UserRole::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'getId'])
            ->getMock();
        $userRole->expects($this->once())
            ->method('getId')
            ->willReturn(77);
        $this->roleManagement->expects($this->once())
            ->method('getRolesByCompanyId')
            ->with($companyId)
            ->willReturn([$userRole]);

        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'getCompanyId'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willReturnSelf();
        $extensionAttributes->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customer);

        $this->userRoleManagement->assignRoles($userId, [$role]);
    }

    /**
     * Test getRolesByUserId method.
     *
     * @param bool $isCompanyAdmin
     * @param array $userRole
     * @param int|null $roleCollectionSize
     * @param array $expectedResult
     * @return void
     * @dataProvider getRolesByUserIdDataProvider
     */
    public function testGetRolesByUserId($isCompanyAdmin, array $userRole, $roleCollectionSize, array $expectedResult)
    {
        $userId = 1;
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $userRoleCollection = $this->createPartialMock(
            \Magento\Company\Model\ResourceModel\UserRole\Collection::class,
            ['addFieldToFilter', 'load', 'getItems']
        );
        $roleCollection = $this->createPartialMock(
            \Magento\Company\Model\ResourceModel\Role\Collection::class,
            ['addFieldToFilter', 'load', 'getSize', 'getItems']
        );
        $this->companyAdminPermission->expects($this->once())
            ->method('isGivenUserCompanyAdmin')
            ->with($userId)
            ->willReturn($isCompanyAdmin);
        if ($isCompanyAdmin) {
            $this->roleManagement->expects($this->once())
                ->method('getAdminRole')
                ->willReturn($role);
        } else {
            $this->userRoleCollectionFactory->expects($this->once())->method('create')->willReturn($userRoleCollection);
            $userRoleCollection->expects($this->once())
                ->method('addFieldToFilter')
                ->with('user_id', ['eq' => $userId])
                ->willReturnSelf();
            $userRoleCollection->expects($this->once())->method('load')->willReturnSelf();
            $userRoleCollection->expects($this->once())->method('getItems')->willReturn($userRole);
            if (!empty($userRole)) {
                $userRole[0]->expects($this->atLeastOnce())->method('getRoleId')->willReturn(1);
                $this->roleCollectionFactory->expects($this->once())->method('create')->willReturn($roleCollection);
                $roleCollection->expects($this->once())
                    ->method('addFieldToFilter')
                    ->with('role_id', ['in' => [1]])
                    ->willReturnSelf();
                $roleCollection->expects($this->once())->method('load')->willReturnSelf();
                $roleCollection->expects($this->once())->method('getSize')->willReturn($roleCollectionSize);
                if ($roleCollectionSize) {
                    $roleCollection->expects($this->once())->method('getItems')->willReturn($expectedResult);
                }
            }
        }

        $this->assertEquals($expectedResult, $this->userRoleManagement->getRolesByUserId($userId));
    }

    /**
     * Test for getRolesByUserId() method if NoSuchEntityException for company admin appeared.
     *
     * @return void
     */
    public function testGetRolesByUserIdNoSuchEntityException()
    {
        $userId = 1;
        $expectedResult = [];
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->companyAdminPermission->expects($this->once())
            ->method('isGivenUserCompanyAdmin')
            ->with($userId)
            ->willThrowException($exception);
        $this->roleManagement->expects($this->never())
            ->method('getAdminRole');
        $userRoleCollection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\UserRole\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'load', 'getItems'])
            ->getMock();
        $this->userRoleCollectionFactory->expects($this->once())->method('create')->willReturn($userRoleCollection);
        $userRoleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('user_id', ['eq' => $userId])
            ->willReturnSelf();
        $userRoleCollection->expects($this->once())->method('load')->willReturnSelf();
        $userRoleCollection->expects($this->once())->method('getItems')->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->userRoleManagement->getRolesByUserId($userId));
    }

    /**
     * Data provider for getRolesByUserId method.
     *
     * @return array
     */
    public function getRolesByUserIdDataProvider()
    {
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $userRole = $this->createPartialMock(\Magento\Company\Model\UserRole::class, ['getRoleId']);
        $roleModel = $this->createMock(\Magento\Company\Model\Role::class);
        return [
            [true, [], null, [$role]],
            [false, [], null, []],
            [false, [$userRole], null, []],
            [false, [$userRole], 1, [$roleModel]],
        ];
    }

    /**
     * Test getUsersByRoleId method.
     *
     * @return void
     */
    public function testGetUsersByRoleId()
    {
        $roleId = 1;
        $userIds = [3, 4, 5];
        $usersMockArray = ['user1', 'user2', 'user3',];

        $userRoleCollection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\UserRole\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'load', 'getItems', 'getColumnValues'])
            ->getMock();
        $this->userRoleCollectionFactory->expects($this->once())->method('create')->willReturn($userRoleCollection);
        $userRoleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('role_id', ['eq' => $roleId])
            ->willReturnSelf();
        $userRoleCollection->expects($this->once())->method('getColumnValues')->willReturn($userIds);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with('entity_id', $userIds, 'in')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->customerRepository->expects($this->once())->method('getList')->with($searchCriteria)->willReturnSelf();
        $this->customerRepository->expects($this->once())->method('getItems')->willReturn($usersMockArray);
        $this->assertEquals($usersMockArray, $this->userRoleManagement->getUsersByRoleId($roleId));
    }

    /**
     * Test getUsersCountByRoleId method.
     *
     * @return void
     */
    public function testGetUsersCountByRoleId()
    {
        $roleId = 1;
        $userRoleCollection = $this->createPartialMock(
            \Magento\Company\Model\ResourceModel\UserRole\Collection::class,
            ['addFieldToFilter', 'getSize']
        );
        $this->userRoleCollectionFactory->expects($this->once())->method('create')->willReturn($userRoleCollection);
        $userRoleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('role_id', ['eq' => $roleId])
            ->willReturnSelf();
        $userRoleCollection->expects($this->once())->method('getSize')->willReturn(1);

        $this->assertEquals(1, $this->userRoleManagement->getUsersCountByRoleId($roleId));
    }

    /**
     * Test deleteRoles method.
     *
     * @return void
     */
    public function testDeleteRoles()
    {
        $userRoleCollectionMock = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\UserRole\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getItems', 'addFieldToFilter', 'load'])
            ->getMock();
        $userRoleCollectionMock->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $userRoleCollectionMock->expects($this->once())->method('load')->will($this->returnSelf());
        $userRole1 = $this->getMockBuilder(\Magento\Company\Model\UserRole::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();
        $userRole2 = $this->getMockBuilder(\Magento\Company\Model\UserRole::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();
        $userRoleCollectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$userRole1, $userRole2]));

        $userRole1->expects($this->once())->method('delete');
        $userRole2->expects($this->once())->method('delete');

        $this->aclDataCacheMock->expects($this->once())
            ->method('clean');

        $this->userRoleCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($userRoleCollectionMock));

        $this->userRoleManagement->deleteRoles(1);
    }
}
