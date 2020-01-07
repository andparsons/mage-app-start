<?php

namespace Magento\Company\Test\Unit\Plugin\Webapi\Controller;

/**
 * Class RestPluginTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permission;

    /**
     * @var \Magento\Customer\Controller\Account\Logout|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logoutAction;

    /**
     * @var \Magento\Framework\Webapi\ErrorProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $errorProcessor;

    /**
     * @var \Magento\Company\Plugin\Webapi\Controller\CustomerResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResolver;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\Company\Plugin\Webapi\Controller\RestPlugin
     */
    private $plugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->permission = $this
            ->getMockBuilder(\Magento\Company\Model\Customer\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoginAllowed'])
            ->getMockForAbstractClass();
        $this->logoutAction = $this->createMock(
            \Magento\Customer\Controller\Account\Logout::class
        );
        $this->errorProcessor = $this->createPartialMock(
            \Magento\Framework\Webapi\ErrorProcessor::class,
            ['maskException']
        );
        $this->customerResolver = $this->createPartialMock(
            \Magento\Company\Plugin\Webapi\Controller\CustomerResolver::class,
            ['getCustomer']
        );
        $this->response = $this->createPartialMock(
            \Magento\Framework\Webapi\Rest\Response::class,
            ['setException']
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Webapi\Controller\RestPlugin::class,
            [
                'permission' => $this->permission,
                'logoutAction' => $this->logoutAction,
                'errorProcessor' => $this->errorProcessor,
                'customerResolver' => $this->customerResolver,
                'response' => $this->response,
            ]
        );
    }

    /**
     * Test aroundDispatch method.
     *
     * @return void
     */
    public function testAroundDispatch()
    {
        $subject = $this->createMock(
            \Magento\Webapi\Controller\Rest::class
        );
        $request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $customer = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $redirect = $this->createMock(
            \Magento\Framework\Controller\Result\Redirect::class
        );
        $phrase = new \Magento\Framework\Phrase('The consumer isn\'t authorized to access resource.');
        $exception = new \Magento\Framework\Exception\AuthorizationException($phrase);
        $webapiException = $this->createMock(
            \Magento\Framework\Webapi\Exception::class
        );
        $proceed = function ($request) {
            return true;
        };
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $this->customerResolver->expects($this->once())->method('getCustomer')->willReturn($customer);
        $this->permission->expects($this->once())->method('isLoginAllowed')->with($customer)->willReturn(false);
        $this->logoutAction->expects($this->once())->method('execute')->willReturn($redirect);
        $this->errorProcessor->expects($this->once())
            ->method('maskException')
            ->with($exception)
            ->willReturn($webapiException);
        $this->response->expects($this->once())->method('setException')->with($webapiException)->willReturnSelf();

        $this->assertEquals($this->response, $this->plugin->aroundDispatch($subject, $proceed, $request));
    }

    /**
     * Test aroundDispatch method with guest customer.
     *
     * @return void
     */
    public function testAroundDispatchWithGuestCustomer()
    {
        $request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $subject = $this->createMock(
            \Magento\Webapi\Controller\Rest::class
        );
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $this->customerResolver->expects($this->once())->method('getCustomer')->willReturn(null);
        $proceed = function ($request) {
            return true;
        };
        $this->assertEquals(true, $this->plugin->aroundDispatch($subject, $proceed, $request));
    }
}
