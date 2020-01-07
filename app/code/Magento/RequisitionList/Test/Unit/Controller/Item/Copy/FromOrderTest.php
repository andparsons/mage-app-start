<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Item\Copy;

/**
 * Unit test for FromOrder controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FromOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestValidator;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Order\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * @var \Magento\RequisitionList\Controller\Item\Copy\FromOrder
     */
    private $fromOrder;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requestValidator = $this->getMockBuilder(\Magento\RequisitionList\Model\Action\RequestValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderRepository = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListRepository = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->converter = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionList\Order\Converter::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fromOrder = $objectManager->getObject(
            \Magento\RequisitionList\Controller\Item\Copy\FromOrder::class,
            [
                'requestValidator' => $this->requestValidator,
                'orderRepository' => $this->orderRepository,
                'requisitionListRepository' => $this->requisitionListRepository,
                'converter' => $this->converter,
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
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
        $orderId = 1;
        $listId = 2;
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRefererUrl'])
            ->getMockForAbstractClass();
        $result->expects($this->atLeastOnce())->method('setRefererUrl')->willReturnSelf();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->request->expects($this->atLeastOnce())->method('getParam')->withConsecutive(['order_id'], ['list_id'])
            ->willReturnOnConsecutiveCalls($orderId, $listId);
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepository->expects($this->atLeastOnce())->method('get')->with($orderId)->willReturn($order);
        $requisitionList = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requisitionList->expects($this->atLeastOnce())->method('getName')->willReturn('name');
        $this->requisitionListRepository->expects($this->atLeastOnce())->method('get')->with($listId)
            ->willReturn($requisitionList);
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->converter->expects($this->atLeastOnce())->method('addItems')->with($order, $requisitionList)
            ->willReturn([$requisitionListItem]);
        $this->messageManager->expects($this->atLeastOnce())->method('addSuccessMessage');

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->fromOrder->execute());
    }

    /**
     * Test for execute method with request validation errors.
     *
     * @return void
     */
    public function testExecuteWithRequestValidationErrors()
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->requestValidator->expects($this->once())->method('getResult')->with($this->request)->willReturn($result);

        $this->assertEquals($result, $this->fromOrder->execute());
    }

    /**
     * Test for execute method with NoSuchEntityException.
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $orderId = 1;
        $listId = 2;
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRefererUrl'])
            ->getMockForAbstractClass();
        $result->expects($this->atLeastOnce())->method('setRefererUrl')->willReturnSelf();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->request->expects($this->atLeastOnce())->method('getParam')->withConsecutive(['order_id'], ['list_id'])
            ->willReturnOnConsecutiveCalls($orderId, $listId);
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('Exception'));
        $this->orderRepository->expects($this->atLeastOnce())->method('get')->willThrowException($exception);
        $this->messageManager->expects($this->atLeastOnce())->method('addErrorMessage');

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->fromOrder->execute());
    }

    /**
     * Test for execute method with Exceptions.
     *
     * @dataProvider exceptionDataProvider
     * @param string $exceptionClass
     * @param string $errorMessage
     *
     * @return void
     */
    public function testExecuteWithLocalizedException($exceptionClass, $errorMessage)
    {
        $orderId = 1;
        $listId = 2;
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRefererUrl'])
            ->getMockForAbstractClass();
        $result->expects($this->atLeastOnce())->method('setRefererUrl')->willReturnSelf();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->request->expects($this->atLeastOnce())->method('getParam')->withConsecutive(['order_id'], ['list_id'])
            ->willReturnOnConsecutiveCalls($orderId, $listId);
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepository->expects($this->atLeastOnce())->method('get')->willReturn($order);
        $exception = new $exceptionClass(__($errorMessage));
        $requisitionList = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListRepository->expects($this->atLeastOnce())->method('get')->with($listId)
            ->willReturn($requisitionList);
        $this->converter->expects($this->atLeastOnce())->method('addItems')->willThrowException($exception);
        $this->messageManager->expects($this->atLeastOnce())->method('addErrorMessage')->with($errorMessage);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->fromOrder->execute());
    }

    /**
     * Data provider for the testExecuteWithLocalizedException test
     *
     * @return array
     */
    public function exceptionDataProvider()
    {
        return [
            [\Magento\Framework\Exception\LocalizedException::class, 'Localized error message'],
            [\Magento\Framework\Exception\CouldNotSaveException::class, 'Could not save error message'],
            [\Magento\Framework\Exception\NoSuchEntityException::class, 'No such entity message']
        ];
    }
}
