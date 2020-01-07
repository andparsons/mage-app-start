<?php

namespace Magento\Company\Test\Unit\Plugin\Sales\Controller\Order;

/**
 * Unit test for \Magento\Company\Plugin\Sales\Controller\Order\ViewPlugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStructure;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Company\Plugin\Sales\Controller\Order\ViewPlugin
     */
    private $viewPlugin;

    /**
     * @var \Magento\Sales\Controller\Order\View|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controller;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resultRedirectFactory =
            $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepository = $this
            ->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->authorization = $this
            ->getMockBuilder(\Magento\Company\Api\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->companyStructure = $this
            ->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userContext = $this
            ->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId'])
            ->getMockForAbstractClass();

        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->setMethods(['isCurrentUserCompanyUser', 'isModuleActive'])
            ->disableOriginalConstructor()->getMock();

        $this->controller = $this->getMockBuilder(\Magento\Sales\Controller\Order\View::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->viewPlugin = $objectManager->getObject(
            \Magento\Company\Plugin\Sales\Controller\Order\ViewPlugin::class,
            [
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'orderRepository' => $this->orderRepository,
                'request' => $this->request,
                'authorization' => $this->authorization,
                'companyStructure' => $this->companyStructure,
                'userContext' => $this->userContext,
                'companyContext' => $this->companyContext
            ]
        );
    }

    /**
     * Test aroundExecute() method.
     *
     * @return void
     */
    public function testAroundExecute()
    {
        $closure = function () {
            return;
        };
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(0);
        $this->assertEquals($closure(), $this->viewPlugin->aroundExecute($this->controller, $closure));
    }

    /**
     * Test aroundExecute() method with exception.
     *
     * @return void
     */
    public function testAroundExecuteWithException()
    {
        $orderId = 1;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $closure = function () use ($result) {
            return $result;
        };
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(2);
        $this->request->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $this->orderRepository->expects($this->once())->method('get')->with($orderId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertEquals($result, $this->viewPlugin->aroundExecute($this->controller, $closure));
    }

    /**
     * Test aroundExecute() method with disabled module.
     *
     * @return void
     */
    public function testAroundExecuteWithDisabledModule()
    {
        $orderId = 1;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $closure = function () use ($result) {
            return $result;
        };
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(2);
        $this->request->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderRepository->expects($this->once())->method('get')->with($orderId)->willReturn($order);
        $order->expects($this->once())->method('getCustomerId')->willReturn(3);
        $this->authorization->expects($this->once())
            ->method('isAllowed')->with('Magento_Sales::view_orders_sub')->willReturn(true);
        $this->companyContext->expects($this->once())->method('isModuleActive')->willReturn(false);
        $this->companyContext->expects($this->once())->method('isCurrentUserCompanyUser')->willReturn(true);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultRedirect->expects($this->once())->method('setPath')->with('company/accessdenied')->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->viewPlugin->aroundExecute($this->controller, $closure));
    }

    /**
     * Test aroundExecute() method without view permissions.
     *
     * @return void
     */
    public function testAroundExecuteWithoutViewPermissions()
    {
        $orderId = 1;
        $customerId = 2;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $closure = function () use ($result) {
            return $result;
        };
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn($customerId);
        $this->request->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderRepository->expects($this->once())->method('get')->with($orderId)->willReturn($order);
        $order->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->companyContext->expects($this->atLeastOnce())->method('isCurrentUserCompanyUser')->willReturn(true);
        $this->authorization->expects($this->once())
            ->method('isAllowed')->with('Magento_Sales::view_orders')->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultRedirect->expects($this->once())->method('setPath')->with('company/accessdenied')->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->viewPlugin->aroundExecute($this->controller, $closure));
    }

    /**
     * Test aroundExecute() method without view children permissions.
     *
     * @return void
     */
    public function testAroundExecuteWithoutViewChildrenPermissions()
    {
        $orderId = 1;
        $customerId = 2;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $closure = function () use ($result) {
            return $result;
        };
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn($customerId);
        $this->request->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderRepository->expects($this->once())->method('get')->with($orderId)->willReturn($order);
        $order->expects($this->once())->method('getCustomerId')->willReturn(3);
        $this->authorization->expects($this->atLeastOnce())->method('isAllowed')
            ->withConsecutive(['Magento_Sales::view_orders_sub'], ['Magento_Sales::view_orders'])->willReturn(true);
        $this->companyContext->expects($this->once())->method('isModuleActive')->willReturn(true);
        $this->companyContext->expects($this->atLeastOnce())->method('isCurrentUserCompanyUser')->willReturn(false);
        $this->companyStructure->expects($this->once())
            ->method('getAllowedChildrenIds')->with($customerId)->willReturn([4, 5]);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultRedirect->expects($this->once())->method('setPath')->with('noroute')->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->viewPlugin->aroundExecute($this->controller, $closure));
    }

    /**
     * Test aroundExecute() method with result.
     *
     * @return void
     */
    public function testAroundExecuteWithResult()
    {
        $orderId = 1;
        $customerId = 2;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $closure = function () use ($result) {
            return $result;
        };
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn($customerId);
        $this->request->expects($this->once())->method('getParam')->with('order_id')->willReturn($orderId);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderRepository->expects($this->once())->method('get')->with($orderId)->willReturn($order);
        $order->expects($this->once())->method('getCustomerId')->willReturn(3);
        $this->authorization->expects($this->atLeastOnce())->method('isAllowed')
            ->withConsecutive(['Magento_Sales::view_orders_sub'], ['Magento_Sales::view_orders'])->willReturn(true);
        $this->companyContext->expects($this->once())->method('isModuleActive')->willReturn(true);
        $this->companyContext->expects($this->atLeastOnce())->method('isCurrentUserCompanyUser')->willReturn(true);
        $this->companyStructure->expects($this->once())
            ->method('getAllowedChildrenIds')->with($customerId)->willReturn([3, 4, 5]);
        $this->assertEquals($result, $this->viewPlugin->aroundExecute($this->controller, $closure));
    }
}
