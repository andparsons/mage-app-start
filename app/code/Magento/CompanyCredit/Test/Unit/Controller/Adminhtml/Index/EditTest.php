<?php

namespace Magento\CompanyCredit\Test\Unit\Controller\Adminhtml\Index;

/**
 * Class EditTest.
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyRepository;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\CompanyCredit\Controller\Adminhtml\Index\Edit
     */
    private $edit;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->historyRepository = $this->createMock(
            \Magento\CompanyCredit\Model\HistoryRepositoryInterface::class
        );
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->edit = $objectManager->getObject(
            \Magento\CompanyCredit\Controller\Adminhtml\Index\Edit::class,
            [
                'historyRepository' => $this->historyRepository,
                'logger' => $this->logger,
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
                'serializer' => $serializer
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $historyId = 2;
        $historyComments = ['History Comment'];
        $reimburseBalance = $this->prepareMocks($historyId);
        $this->request->expects($this->at(2))->method('getParam')->with('history_id')->willReturn($historyId);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $history = $this->createMock(\Magento\CompanyCredit\Model\HistoryInterface::class);
        $this->historyRepository->expects($this->once())->method('get')->with($historyId)->willReturn($history);
        $history->expects($this->once())->method('setPurchaseOrder')
            ->with($reimburseBalance['purchase_order'])->willReturnSelf();
        $history->expects($this->exactly(2))->method('getComment')->willReturn(json_encode($historyComments));
        $history->expects($this->once())->method('setComment')
            ->with(json_encode($historyComments + ['custom' => $reimburseBalance['credit_comment']]))->willReturnSelf();
        $this->historyRepository->expects($this->once())->method('save')->willReturn($history);
        $result->expects($this->once())->method('setData')->with(['status' => 'success'])->willReturnSelf();
        $this->assertEquals($result, $this->edit->execute());
    }

    /**
     * Test for method execute with NoSuchEntityException.
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $historyId = null;
        $this->prepareMocks($historyId);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $this->historyRepository->expects($this->once())->method('get')->with($historyId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $result->expects($this->once())->method('setData')
            ->with(['status' => 'error', 'error' => __('History record no longer exists.')])->willReturnSelf();
        $this->assertEquals($result, $this->edit->execute());
    }

    /**
     * Test for method execute with CouldNotSaveException.
     *
     * @return void
     */
    public function testExecuteWithCouldNotSaveException()
    {
        $historyId = 2;
        $historyComments = ['History Comment'];
        $exceptionMessage = 'Could not save company limit.';
        $reimburseBalance = $this->prepareMocks($historyId);
        $this->request->expects($this->at(2))->method('getParam')->with('history_id')->willReturn($historyId);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $history = $this->createMock(\Magento\CompanyCredit\Model\HistoryInterface::class);
        $this->historyRepository->expects($this->once())->method('get')->with($historyId)->willReturn($history);
        $history->expects($this->once())->method('setPurchaseOrder')
            ->with($reimburseBalance['purchase_order'])->willReturnSelf();
        $history->expects($this->exactly(2))->method('getComment')->willReturn(json_encode($historyComments));
        $history->expects($this->once())->method('setComment')
            ->with(json_encode($historyComments + ['custom' => $reimburseBalance['credit_comment']]))->willReturnSelf();
        $this->historyRepository->expects($this->once())->method('save')
            ->willThrowException(new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage)));
        $result->expects($this->once())->method('setData')
            ->with(['status' => 'error', 'error' => $exceptionMessage])->willReturnSelf();
        $this->assertEquals($result, $this->edit->execute());
    }

    /**
     * Test for method execute with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $historyId = null;
        $exception = new \Exception('Some exception message');
        $this->prepareMocks($historyId);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $this->historyRepository->expects($this->once())->method('get')->with($historyId)
            ->willThrowException($exception);
        $result->expects($this->once())->method('setData')
            ->with(['status' => 'error', 'error' => __('Something went wrong. Please try again later.')])
            ->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exception)->willReturn(null);
        $this->assertEquals($result, $this->edit->execute());
    }

    /**
     * Prepare mocks.
     *
     * @param int $historyId
     * @return array
     */
    private function prepareMocks($historyId)
    {
        $reimburseBalance = [
            'purchase_order' => 'O123',
            'credit_comment' => 'Some Comment',
        ];
        $this->request->expects($this->at(0))->method('getParam')
            ->with('reimburse_balance')->willReturn($reimburseBalance);
        $this->request->expects($this->at(1))->method('getParam')->with('history_id')->willReturn($historyId);
        return $reimburseBalance;
    }
}
