<?php
namespace Magento\Company\Test\Unit\Controller\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for customer create controller.
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Company\Controller\Customer\Create
     */
    private $create;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContextMock;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManagerMock;

    /**
     * @var \Magento\Company\Model\Action\SaveCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerActionMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyContextMock = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureManagerMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerActionMock = $this->getMockBuilder(\Magento\Company\Model\Action\SaveCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->create = $this->objectManagerHelper->getObject(
            \Magento\Company\Controller\Customer\Create::class,
            [
                'companyContext' => $this->companyContextMock,
                'structureManager' => $this->structureManagerMock,
                'customerAction' => $this->customerActionMock,
                '_request' => $this->requestMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    /**
     * Test for Execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $this->requestMock->expects($this->once())->method('getParam')->with('target_id')->willReturn(1);
        $this->companyContextMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManagerMock->expects($this->once())->method('getAllowedIds')->with($customerId)
            ->willReturn([
                'structures' => [$customerId]
            ]);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['__toArray'])
            ->getMockForAbstractClass();
        $this->customerActionMock->expects($this->once())->method('create')->with($this->requestMock)
            ->willReturn($customerMock);
        $customerMock->expects($this->once())->method('__toArray')->willReturn([]);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())->method('setData')
            ->with([
                'status' => 'ok',
                'message' => __('The customer was successfully created.'),
                'data' => []
            ])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($resultJson);

        $this->create->execute();
    }

    /**
     * Test for Execute method when customer ID is not allowed.
     *
     * @return void
     */
    public function testExecuteWhenCustomerIdNotAllowed()
    {
        $customerId = 1;
        $this->requestMock->expects($this->once())->method('getParam')->with('target_id')->willReturn(1);
        $this->companyContextMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManagerMock->expects($this->once())->method('getAllowedIds')->with($customerId)
            ->willReturn([
                'structures' => [2]
            ]);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())->method('setData')
            ->with([
                'status' => 'error',
                'message' => __('You are not allowed to do this.'),
                'payload' => []
            ])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($resultJson);

        $this->create->execute();
    }

    /**
     * Test for Execute method when target_id parameter is absent.
     *
     * @return void
     */
    public function testExecuteWhenTargetIdAbsent()
    {
        $customerId = 1;
        $this->requestMock->expects($this->once())->method('getParam')->with('target_id')->willReturn(null);
        $this->companyContextMock->expects($this->any())->method('getCustomerId')->willReturn($customerId);
        $this->structureManagerMock->expects($this->once())->method('getAllowedIds')->with($customerId)
            ->willReturn([
                'structures' => [$customerId]
            ]);
        $this->structureManagerMock->expects($this->once())->method('getStructureByCustomerId')->with($customerId)
            ->willReturn(null);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())->method('setData')
            ->with([
                'status' => 'error',
                'message' => __('Cannot create the customer.'),
                'payload' => []
            ])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($resultJson);

        $this->create->execute();
    }

    /**
     * Test for Execute method when InputMismatchException is thrown.
     *
     * @return void
     */
    public function testExecuteWithInputMismatchException()
    {
        $exception = new \Magento\Framework\Exception\State\InputMismatchException(__('Exception message'));
        $this->prepareExcepionMocks($exception);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())->method('setData')
            ->with([
                'status' => 'error',
                'message' => __(
                    'A user with this email address already exists in the system. '
                    . 'Enter a different email address to create this user.'
                ),
                'payload' => [
                    'field' => 'email'
                ]
            ])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($resultJson);

        $this->create->execute();
    }

    /**
     * Test for Execute method when LocalizedException is thrown.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $exception = new \Magento\Framework\Exception\LocalizedException(__('Exception message'));
        $this->prepareExcepionMocks($exception);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())->method('setData')
            ->with([
                'status' => 'error',
                'message' => __('Exception message'),
                'payload' => []
            ])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($resultJson);

        $this->create->execute();
    }

    /**
     * Test for Execute method when Exception is thrown.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception(__('Something went wrong.'));
        $this->prepareExcepionMocks($exception);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())->method('setData')
            ->with([
                'status' => 'error',
                'message' => __('Something went wrong.'),
                'payload' => []
            ])
            ->willReturnSelf();
        $this->resultFactoryMock->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($resultJson);

        $this->create->execute();
    }

    /**
     * Prepare mocks for tests with Exceptions.
     *
     * @param \Exception $exception
     * @return void
     */
    private function prepareExcepionMocks(\Exception $exception)
    {
        $customerId = 1;
        $this->requestMock->expects($this->once())->method('getParam')->with('target_id')->willReturn(1);
        $this->companyContextMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManagerMock->expects($this->once())->method('getAllowedIds')->with($customerId)
            ->willReturn([
                'structures' => [$customerId]
            ]);

        $this->customerActionMock->expects($this->once())->method('create')->with($this->requestMock)
            ->willThrowException($exception);
    }
}
