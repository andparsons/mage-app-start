<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Class for test RoleRepository.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RoleRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\RoleInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Role|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleResource;

    /**
     * @var \Magento\Company\Model\ResourceModel\Role\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleCollectionFactory;

    /**
     * @var \Magento\Company\Api\Data\RoleSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Permission\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Model\Role|\PHPUnit_Framework_MockObject_MockObject
     */
    private $role;

    /**
     * @var \Magento\Company\Model\Role\Permission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rolePermission;

    /**
     * @var \Magento\Company\Model\RoleRepository
     */
    private $roleRepository;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var \Magento\Company\Model\PermissionManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionManagement;

    /**
     * @var \Magento\Company\Model\Role\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->roleFactory = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->roleResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Role::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'delete', 'load'])
            ->getMock();
        $this->roleCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Role\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultsFactory = $this->getMockBuilder(
            \Magento\Company\Api\Data\RoleSearchResultsInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->permissionCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Permission\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create'])
            ->getMock();
        $this->role = $this->getMockBuilder(\Magento\Company\Model\Role::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRoleName', 'getCompanyId', 'getId', 'getPermissions', 'load', 'setPermissions'])
            ->getMock();
        $this->aclDataCacheMock = $this->getMockBuilder(\Magento\Framework\Acl\Data\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $userRoleManagement = $this->getMockBuilder(\Magento\Company\Api\AclInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->rolePermission = $this->getMockBuilder(\Magento\Company\Model\Role\Permission::class)
            ->setMethods([
                'delete',
                'setRoleId',
                'save',
                'saveRolePermissions',
                'deleteRolePermissions',
                'getRolePermissions',
                'getRoleUsersCount'
            ])
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                'permissionCollectionFactory' => $this->permissionCollectionFactory,
                'aclDataCache' => $this->aclDataCacheMock,
                'userRoleManagement' => $userRoleManagement
            ])
            ->getMock();
        $this->permissionManagement = $this->getMockBuilder(\Magento\Company\Model\PermissionManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['retrieveAllowedResources', 'populatePermissions'])
            ->getMockForAbstractClass();
        $this->validator = $this->getMockBuilder(\Magento\Company\Model\Role\Validator::class)
            ->disableOriginalConstructor()
            ->setMethods(['retrieveRole', 'validateRoleBeforeDelete', 'checkRoleExist'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->roleRepository = $objectManager->getObject(
            \Magento\Company\Model\RoleRepository::class,
            [
                'roleFactory' => $this->roleFactory,
                'roleResource' => $this->roleResource,
                'roleCollectionFactory' => $this->roleCollectionFactory,
                'searchResultsFactory' => $this->searchResultsFactory,
                'rolePermission' => $this->rolePermission,
                'permissionManagement' => $this->permissionManagement,
                'validator' => $this->validator
            ]
        );
    }

    /**
     * Test save method.
     *
     * @return void
     */
    public function testSave()
    {
        $roleId = 1;
        $this->validateRoleName(false);
        $this->role->expects($this->atLeastOnce())->method('getId')->willReturn($roleId);
        $this->roleResource->expects($this->once())->method('save')->with($this->role)->willReturnSelf();
        $this->validator->expects($this->once())
            ->method('retrieveRole')
            ->with($this->role)
            ->willReturn($this->role);
        $this->role->expects($this->once())
            ->method('getPermissions')
            ->willReturn($this->getPermissionArray());
        $this->permissionManagement->expects($this->once())
            ->method('retrieveAllowedResources')
            ->willReturn([7]);
        $this->permissionManagement->expects($this->once())
            ->method('populatePermissions')
            ->willReturn([$this->mockPermission()]);
        $this->rolePermission->expects($this->once())->method('saveRolePermissions')->willReturnSelf();
        $this->assertEquals($this->role, $this->roleRepository->save($this->role));
    }

    /**
     * Test save method throws exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage User role with this name already exists. Enter a different name to save this role.
     */
    public function testSaveRoleWithException()
    {
        $roleId = 1;
        $this->validateRoleName(true);
        $this->role->expects($this->atLeastOnce())->method('getId')->willReturn($roleId);
        $this->validator->expects($this->once())
            ->method('retrieveRole')
            ->with($this->role)
            ->willReturn($this->role);
        $this->role->expects($this->once())
            ->method('getPermissions')
            ->willReturn($this->getPermissionArray());
        $this->permissionManagement->expects($this->once())
            ->method('retrieveAllowedResources')
            ->willReturn([7]);
        $this->permissionManagement->expects($this->once())
            ->method('populatePermissions')
            ->willReturn([$this->mockPermission()]);
        $this->assertEquals($this->role, $this->roleRepository->save($this->role));
    }

    /**
     * Test save method throws CouldNotSaveException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save role
     */
    public function testSaveRoleWithCouldNotSaveException()
    {
        $roleId = 1;
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__('Could not save role'));
        $this->validateRoleName(false);
        $this->role->expects($this->atLeastOnce())->method('getId')->willReturn($roleId);
        $this->validator->expects($this->once())
            ->method('retrieveRole')
            ->with($this->role)
            ->willReturn($this->role);
        $this->role->expects($this->once())
            ->method('getPermissions')
            ->willReturn($this->getPermissionArray());
        $this->permissionManagement->expects($this->once())
            ->method('retrieveAllowedResources')
            ->willReturn([7]);
        $this->permissionManagement->expects($this->once())
            ->method('populatePermissions')
            ->willReturn([$this->mockPermission()]);
        $this->roleResource->expects($this->once())->method('save')->willThrowException($exception);
        $this->roleRepository->save($this->role);
    }

    /**
     * Test get method.
     *
     * @return void
     */
    public function testGet()
    {
        $roleId = 1;
        $this->roleFactory->expects($this->once())->method('create')->willReturn($this->role);
        $this->rolePermission->expects($this->once())
            ->method('getRolePermissions')
            ->willReturn([$this->getPermissionArray()]);

        $this->assertEquals($this->role, $this->roleRepository->get($roleId));
    }

    /**
     * Test get method throws NoSuchEntityException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with roleId = 2
     */
    public function testGetWithException()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(
            __('No such entity with %fieldName = %fieldValue', ['fieldName' => 'roleId', 'fieldValue' => 2])
        );
        $this->roleResource->expects($this->once())
            ->method('load')
            ->with($this->role, 2)
            ->willReturn($this->role);
        $this->roleFactory->expects($this->once())->method('create')->willReturn($this->role);
        $this->validator->expects($this->once())
            ->method('checkRoleExist')
            ->with($this->role, 2)
            ->willThrowException($exception);

        $this->roleRepository->get(2);
    }

    /**
     * Test delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $roleId = 1;
        $this->roleResource->expects($this->once())->method('delete')->with($this->role)->willReturnSelf();
        $this->prepareRolePermissions($roleId);
        $this->roleResource->expects($this->once())
            ->method('delete')
            ->with($this->role)
            ->willReturnSelf();
        $this->rolePermission->expects($this->once())->method('deleteRolePermissions')->willReturnSelf();

        $this->assertTrue($this->roleRepository->delete($roleId));
    }

    /**
     * Test delete method throws StateException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Cannot delete role with id 1
     */
    public function testDeleteWithException()
    {
        $roleId = 1;
        $exception = new \Exception();
        $this->role->expects($this->exactly(1))->method('getId')->willReturn($roleId);
        $this->validator->expects($this->once())
            ->method('validateRoleBeforeDelete')
            ->with($this->role)
            ->willReturn(true);
        $this->prepareRolePermissions($roleId);
        $this->roleResource->expects($this->once())
            ->method('delete')
            ->with($this->role)
            ->willThrowException($exception);
        $this->roleRepository->delete($roleId);
    }

    /**
     * Mock validateRoleName.
     *
     * @param bool $totalCount
     * @return void
     */
    private function validateRoleName($totalCount)
    {
        $roleName = 'Custom Role';
        $companyId = 1;
        $roleId = 1;
        $this->role->expects($this->once())->method('getRoleName')->willReturn($roleName);
        $this->role->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $roleCollection = $this->createPartialMock(
            \Magento\Company\Model\ResourceModel\Role\Collection::class,
            ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'getItems']
        );
        $this->roleCollectionFactory->expects($this->once())->method('create')->willReturn($roleCollection);
        $roleCollection->expects($this->exactly(3))
            ->method('addFieldToFilter')
            ->withConsecutive(
                [\Magento\Company\Api\Data\RoleInterface::ROLE_NAME, ['eq' => $roleName]],
                [\Magento\Company\Api\Data\RoleInterface::COMPANY_ID, ['eq' => $companyId]],
                [\Magento\Company\Api\Data\RoleInterface::ROLE_ID, ['neq' => $roleId]]
            )->willReturnSelf();
        $roleCollection->expects($this->once())->method('getSize')->willReturn($totalCount);
    }

    /**
     * Test getList.
     *
     * @return void
     */
    public function testGetList()
    {
        $searchCriteria = $this->createPartialMock(
            \Magento\Framework\Api\SearchCriteria::class,
            ['getFilterGroups', 'getSortOrders', 'getCurrentPage', 'getPageSize']
        );

        $searchResults = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\RoleSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setSearchCriteria', 'setTotalCount', 'setItems', 'getTotalCount']
        );
        $roleCollection = $this->createPartialMock(
            \Magento\Company\Model\ResourceModel\Role\Collection::class,
            ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'getItems']
        );
        $filterGroup = $this->createPartialMock(
            \Magento\Framework\Api\Search\FilterGroup::class,
            ['getFilters']
        );
        $filter = $this->createPartialMock(
            \Magento\Framework\Api\Filter::class,
            ['getConditionType', 'getField', 'getValue']
        );
        $sortOrder = $this->createPartialMock(
            \Magento\Framework\Api\SortOrder::class,
            ['getField', 'getDirection']
        );
        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $searchResults->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria)
            ->willReturnSelf();
        $this->roleCollectionFactory->expects($this->once())->method('create')->willReturn($roleCollection);
        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$filter]);
        $filter->expects($this->once())->method('getConditionType')->willReturn(null);
        $filter->expects($this->once())->method('getField')->willReturn('some_field');
        $filter->expects($this->once())->method('getValue')->willReturn('some_value');
        $roleCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('some_field', ['eq' => 'some_value'])
            ->willReturnSelf();
        $roleCollection->expects($this->once())->method('getSize')->willReturn(1);
        $searchResults->expects($this->once())->method('setTotalCount')->with(1)->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getSortOrders')->willReturn([$sortOrder]);
        $sortOrder->expects($this->once())->method('getField')->willReturn('some_field');
        $sortOrder->expects($this->once())->method('getDirection')->willReturn('ASC');
        $roleCollection->expects($this->once())->method('addOrder')->with('some_field', 'ASC')->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn(1);
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn(1);
        $roleCollection->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $roleCollection->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $roleCollection->expects($this->once())->method('getItems')->willReturn([$this->role]);
        $this->rolePermission->expects($this->once())
            ->method('getRolePermissions')
            ->willReturn([$this->getPermissionArray()]);
        $searchResults->expects($this->once())->method('setItems')->willReturnSelf();

        $this->assertEquals($searchResults, $this->roleRepository->getList($searchCriteria));
    }

    /**
     * Mock Permission.
     *
     * @return \Magento\Company\Api\Data\PermissionInterface
     */
    private function mockPermission()
    {
        $permission = $this->getMockBuilder(\Magento\Company\Model\Permission::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResourceId'])
            ->getMock();
        $permission->expects($this->atLeastOnce())
            ->method('getResourceId')
            ->willReturn(7);

        return $permission;
    }

    /**
     * Method to get dummy permissions.
     *
     * @return array
     */
    private function getPermissionArray()
    {
        return [
            'permission_id' => 4,
            'role_id' => 3,
            'resource_id' => 7,
            'permission' => 1
        ];
    }

    /**
     * Prepate role factory and role permission mocks for test.
     *
     * @param int $roleId
     * @return void
     */
    private function prepareRolePermissions($roleId)
    {
        $this->roleFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->role);
        $this->roleResource->expects($this->atLeastOnce())
            ->method('load')
            ->with($this->role, $roleId)
            ->willReturn($this->role);
        $this->rolePermission->expects($this->atLeastOnce())
            ->method('getRolePermissions')
            ->willReturn($this->getPermissionArray());
    }
}
