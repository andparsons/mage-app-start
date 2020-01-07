<?php

namespace Magento\Company\Test\Unit\Controller\Customer;

/**
 * Unit test for customer save controller.
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Action\SaveCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAction;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Company\Controller\Customer\Save
     */
    private $save;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerAction = $this->getMockBuilder(\Magento\Company\Model\Action\SaveCustomer::class)
            ->disableOriginalConstructor()->getMock();
        $this->structureManager = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()->getMock();
        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->save = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Customer\Save::class,
            [
                'customerAction' => $this->customerAction,
                'structureManager' => $this->structureManager,
                '_request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'logger' => $this->logger,
                'companyContext' => $this->companyContext,
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
        $customerId = 1;
        $customerData = ['customer_data'];
        $this->request->expects($this->once())->method('getParam')->with('customer_id')->willReturn($customerId);
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['users' => [1, 5, 8]]);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['__toArray'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->customerAction->expects($this->once())->method('update')->with($this->request)->willReturn($customer);
        $customer->expects($this->once())->method('__toArray')->willReturn($customerData);
        $this->resultJson->expects($this->once())->method('setData')->with(
            [
                'status' => 'ok',
                'message' => 'The customer was successfully updated.',
                'data' => $customerData,
            ]
        )->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);

        $this->assertEquals($this->resultJson, $this->save->execute());
    }

    /**
     * Test for execute method with localized exception.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $customerId = 1;
        $exceptionMessage = 'Customer save error';
        $this->request->expects($this->once())->method('getParam')->with('customer_id')->willReturn($customerId);
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['users' => [1, 5, 8]]);
        $this->customerAction->expects($this->once())->method('update')->with($this->request)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage)));
        $this->resultJson->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'message' => $exceptionMessage,
                'payload' => [],
            ]
        )->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);

        $this->assertEquals($this->resultJson, $this->save->execute());
    }

    /**
     * Test for execute method with generic exception.
     *
     * @return void
     */
    public function testExecuteWithGenericException()
    {
        $customerId = 1;
        $exception = new \Exception();
        $this->request->expects($this->once())->method('getParam')->with('customer_id')->willReturn($customerId);
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['users' => [1, 5, 8]]);
        $this->customerAction->expects($this->once())
            ->method('update')->with($this->request)->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->resultJson->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'message' => 'Something went wrong.',
                'payload' => [],
            ]
        )->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);

        $this->assertEquals($this->resultJson, $this->save->execute());
    }

    /**
     * Test execute method with InputMismatchException exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     * @expectedExceptionMessage You are not allowed to do this.
     */
    public function testExecuteWithInputMismatchException()
    {
        $customerId = 1;
        $this->request->expects($this->once())->method('getParam')->with('customer_id')->willReturn($customerId);
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['users' => [5, 8]]);

        $this->save->execute();
    }
}
