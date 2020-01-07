<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Requisition;

/**
 * Test for \Magento\RequisitionList\Controller\Requisition\Delete class.
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestValidator;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\RequisitionList\Controller\Requisition\Delete
     */
    private $deleteMock;

    /**
     * @var \Magento\Framework\App\Console\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->createMock(\Magento\Framework\App\Console\Request::class);
        $this->resultFactory =
            $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->requestValidator = $this->createMock(\Magento\RequisitionList\Model\Action\RequestValidator::class);
        $this->requisitionListRepository =
            $this->createMock(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->resultRedirect = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Redirect::class,
            ['setPath', 'setRefererUrl']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->deleteMock = $objectManager->getObject(
            \Magento\RequisitionList\Controller\Requisition\Delete::class,
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'requestValidator' => $this->requestValidator,
                'requisitionListRepository' => $this->requisitionListRepository,
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
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn(null);
        $this->request->expects($this->any())->method('getParam')->willReturn(1);
        $this->prepareResultRedirect();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->deleteMock->execute());
    }

    /**
     * Test execute method not allowed action.
     *
     * @return void
     */
    public function testExecuteWithNotAllowedAction()
    {
        $this->resultRedirect->expects($this->any())->method('setPath')->will($this->returnSelf());
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn($this->resultRedirect);
        $this->requisitionListRepository->expects($this->never())->method('deleteById');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->deleteMock->execute());
    }

    /**
     * Test execute with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn(null);
        $this->request->expects($this->any())->method('getParam')->willReturn(1);
        $this->prepareResultRedirect();
        $exception = new \Exception;
        $this->requisitionListRepository->expects($this->any())->method('deleteById')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->deleteMock->execute());
    }

    /**
     * Test execute with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn(null);
        $this->request->expects($this->any())->method('getParam')->willReturn(1);
        $this->prepareResultRedirect();
        $phrase = new \Magento\Framework\Phrase('exception');
        $localizedException = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->requisitionListRepository->expects($this->any())->method('deleteById')
            ->willThrowException($localizedException);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->deleteMock->execute());
    }

    /**
     * Prepare result redirect.
     *
     * @return void
     */
    private function prepareResultRedirect()
    {
        $this->resultRedirect->expects($this->any())->method('setPath')->willReturnSelf();
        $this->resultRedirect->expects($this->any())->method('setRefererUrl')->willReturnSelf();
        $this->resultFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);
    }
}
