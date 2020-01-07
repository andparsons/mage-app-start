<?php

namespace Magento\Company\Test\Unit\Controller\Role;

/**
 * Test for Delete.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\RoleRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Model\CompanyUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUser;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Controller\Role\Delete
     */
    private $delete;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->roleRepository = $this->getMockBuilder(\Magento\Company\Model\RoleRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyUser = $this->getMockBuilder(\Magento\Company\Model\CompanyUser::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->delete = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Role\Delete::class,
            [
                'roleRepository' => $this->roleRepository,
                'companyUser' => $this->companyUser,
                '_request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
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
        $companyId = 2;
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($roleId);
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()->getMock();
        $role->expects($this->once())->method('getId')->willReturn($roleId);
        $role->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $role->expects($this->once())->method('getRoleName')->willReturn($roleName);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willReturn($role);
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($companyId);
        $this->roleRepository->expects($this->once())->method('delete')->with($roleId)->willReturn(true);
        $this->messageManager->expects($this->once())->method('addSuccessMessage')->with(
            __(
                'You have deleted role %companyRoleName.',
                ['companyRoleName' => $roleName]
            )
        )->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/role/')->willReturnSelf();
        $this->assertEquals($result, $this->delete->execute());
    }

    /**
     * Test for execute method with bad request exception.
     *
     * @return void
     */
    public function testExecuteWithBadRequestException()
    {
        $roleId = 1;
        $companyId = 2;
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($roleId);
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()->getMock();
        $role->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willReturn($role);
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn(null);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')->with('Bad Request')->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/role/')->willReturnSelf();
        $this->assertEquals($result, $this->delete->execute());
    }

    /**
     * Test for execute method with NoSuchEntityException.
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $roleId = 1;
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($roleId);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willThrowException(
            new \Magento\Framework\Exception\NoSuchEntityException()
        );
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')->with('No such entity.')->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/role/')->willReturnSelf();
        $this->assertEquals($result, $this->delete->execute());
    }

    /**
     * Test for execute method with \Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $roleId = 1;
        $exception = new \Exception();
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($roleId);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')->with('Something went wrong. Please try again later.')->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setPath')->with('company/role/')->willReturnSelf();
        $this->assertEquals($result, $this->delete->execute());
    }
}
