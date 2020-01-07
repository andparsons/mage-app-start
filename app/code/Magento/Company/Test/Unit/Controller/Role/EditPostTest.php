<?php

namespace Magento\Company\Test\Unit\Controller\Role;

/**
 * Class EditPostTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Api\Data\RoleInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Model\CompanyUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUser;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\PermissionManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionManagement;

    /**
     * @var \Magento\Company\Controller\Role\EditPost
     */
    private $editPost;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->roleRepository = $this->createMock(\Magento\Company\Model\RoleRepository::class);
        $this->roleFactory = $this->createPartialMock(
            \Magento\Company\Api\Data\RoleInterfaceFactory::class,
            ['create']
        );
        $this->companyUser = $this->createMock(\Magento\Company\Model\CompanyUser::class);
        $this->request = $this->createMock(
            \Magento\Framework\App\RequestInterface::class
        );
        $this->redirect = $this->createMock(
            \Magento\Framework\App\Response\RedirectInterface::class
        );
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->permissionManagement = $this->createMock(
            \Magento\Company\Model\PermissionManagementInterface::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->editPost = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Role\EditPost::class,
            [
                'roleRepository' => $this->roleRepository,
                'roleFactory' => $this->roleFactory,
                'companyUser' => $this->companyUser,
                'permissionManagement' => $this->permissionManagement,
                '_request' => $this->request,
                '_redirect' => $this->redirect,
                '_response' => $this->response,
                'messageManager' => $this->messageManager,
                'logger' => $this->logger,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $roleId = 1;
        $roleName = 'Role 1';
        $rolePermissions = '3';
        $companyId = 2;
        $this->request->expects($this->at(0))->method('getParam')->with('id')->willReturn($roleId);
        $this->request->expects($this->at(1))->method('getParam')->with('role_name')->willReturn($roleName);
        $this->request->expects($this->at(2))
            ->method('getParam')->with('role_permissions')->willReturn($rolePermissions);
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $role->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->roleFactory->expects($this->once())->method('create')->willReturn($role);
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($companyId);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willReturn($role);
        $role->expects($this->once())->method('setRoleName')->with($roleName)->willReturnSelf();
        $role->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $permission = $this->createMock(\Magento\Company\Api\Data\PermissionInterface::class);
        $this->permissionManagement->expects($this->once())->method('populatePermissions')->willReturn([$permission]);
        $role->expects($this->once())->method('setPermissions')->with([$permission])->willReturnSelf();
        $this->roleRepository->expects($this->once())->method('save')->with($role)->willReturn($role);
        $this->redirect->expects($this->once())
            ->method('redirect')->with($this->response, '*/*/', [])->willReturn($this->response);
        $this->assertEquals($this->response, $this->editPost->execute());
    }

    /**
     * Test for execute method with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $roleId = 1;
        $companyId = 2;
        $this->request->expects($this->at(0))->method('getParam')->with('id')->willReturn($roleId);
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willReturn($role);
        $role->expects($this->once())->method('getCompanyId')->willReturn(3);
        $this->roleFactory->expects($this->once())->method('create')->willReturn($role);
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($companyId);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')->with('Bad Request')->willReturnSelf();
        $this->logger->expects($this->once())->method('critical');

        $this->redirect->expects($this->once())
            ->method('redirect')->with($this->response, '*/role/edit', ['id' => $roleId])->willReturn($this->response);
        $this->assertEquals($this->response, $this->editPost->execute());
    }
}
