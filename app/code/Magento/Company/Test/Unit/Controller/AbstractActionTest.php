<?php

namespace Magento\Company\Test\Unit\Controller;

/**
 * Class AbstractActionTest.
 */
class AbstractActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

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
        $this->companyContext = $this->createMock(\Magento\Company\Model\CompanyContext::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->redirect = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->action = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Index\Index::class,
            [
                'companyContext' => $this->companyContext,
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
        $this->companyContext->expects($this->once())->method('isCustomerLoggedIn')->willReturn(true);
        $request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getFullActionName', 'getRouteName', 'isDispatched']
        );
        $this->companyContext->expects($this->once())->method('isModuleActive')->willReturn(true);
        $this->assertEquals($this->response, $this->action->dispatch($request));
    }

    /**
     * Test for execute method with disabled module.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Page not found.
     */
    public function testExecuteWithDisabledModule()
    {
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->companyContext->expects($this->once())->method('isModuleActive')->willReturn(false);
        $this->action->dispatch($request);
    }

    /**
     * Test for execute method with non-authenticated user.
     *
     * @return void
     */
    public function testExecuteWithNonAuthenticatedUser()
    {
        $this->companyContext->expects($this->once())->method('isCustomerLoggedIn')->willReturn(false);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->companyContext->expects($this->once())->method('isModuleActive')->willReturn(true);
        $this->actionFlag->expects($this->once())->method('set')->with('', 'no-dispatch', true);
        $this->redirect->expects($this->once())->method('redirect')
            ->with($this->response, 'customer/account/login', []);
        $this->assertEquals($this->response, $this->action->dispatch($request));
    }
}
