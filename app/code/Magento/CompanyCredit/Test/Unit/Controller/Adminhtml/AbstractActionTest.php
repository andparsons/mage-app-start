<?php

namespace Magento\CompanyCredit\Test\Unit\Controller\Adminhtml;

/**
 * Class AbstractActionTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\StatusServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userContext;

    /**
     * @var \Magento\CompanyCredit\Model\PaymentMethodStatus|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodStatus;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFlag;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\Company\Controller\Index\Index
     */
    private $action;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->moduleConfig = $this->createMock(\Magento\Company\Api\StatusServiceInterface::class);
        $this->userContext = $this->createMock(
            \Magento\Authorization\Model\UserContextInterface::class
        );
        $this->paymentMethodStatus = $this->createMock(
            \Magento\CompanyCredit\Model\PaymentMethodStatus::class
        );
        $this->authorization = $this->createMock(\Magento\Company\Api\AuthorizationInterface::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->redirect = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->action = $objectManagerHelper->getObject(
            \Magento\CompanyCredit\Controller\History\Index::class,
            [
                'moduleConfig' => $this->moduleConfig,
                'userContext' => $this->userContext,
                'paymentMethodStatus' => $this->paymentMethodStatus,
                'authorization' => $this->authorization,
                'logger' => $this->logger,
                '_response' => $this->response,
                '_actionFlag' => $this->actionFlag,
                '_redirect' => $this->redirect,
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
        $this->moduleConfig->expects($this->once())->method('isActive')->willReturn(true);
        $this->paymentMethodStatus->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $this->authorization->expects($this->once())->method('isAllowed')
            ->with(\Magento\CompanyCredit\Controller\AbstractAction::COMPANY_CREDIT_RESOURCE)
            ->willReturn(true);
        $request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getFullActionName', 'getRouteName', 'isDispatched']
        );
        $this->assertEquals($this->response, $this->action->dispatch($request));
    }

    /**
     * Test for execute method with disabled payment.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Page not found.
     */
    public function testExecuteWithDisabledPayment()
    {
        $this->moduleConfig->expects($this->once())->method('isActive')->willReturn(true);
        $this->paymentMethodStatus->expects($this->once())->method('isEnabled')->willReturn(false);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->action->dispatch($request);
    }

    /**
     * Test for execute method with non-authenticated user.
     *
     * @return void
     */
    public function testExecuteWithNonAuthenticatedUser()
    {
        $this->moduleConfig->expects($this->once())->method('isActive')->willReturn(true);
        $this->paymentMethodStatus->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(null);
        $this->actionFlag->expects($this->once())->method('set')->with('', 'no-dispatch', true);
        $this->redirect->expects($this->once())
            ->method('redirect')->with($this->response, 'customer/account/login')->willReturn($this->response);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->assertEquals($this->response, $this->action->dispatch($request));
    }

    /**
     * Test for execute method without permissions.
     *
     * @return void
     */
    public function testExecuteWithoutPermissions()
    {
        $this->moduleConfig->expects($this->once())->method('isActive')->willReturn(true);
        $this->paymentMethodStatus->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $this->authorization->expects($this->once())->method('isAllowed')
            ->with(\Magento\CompanyCredit\Controller\AbstractAction::COMPANY_CREDIT_RESOURCE)
            ->willReturn(false);
        $this->actionFlag->expects($this->once())->method('set')->with('', 'no-dispatch', true);
        $this->redirect->expects($this->once())
            ->method('redirect')->with($this->response, 'noroute')->willReturn($this->response);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->assertEquals($this->response, $this->action->dispatch($request));
    }
}
