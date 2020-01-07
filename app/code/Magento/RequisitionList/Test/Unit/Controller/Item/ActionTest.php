<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Item;

/**
 * Class ActionTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestValidator;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requisitionListItemRepository;

    /**
     * @var \Magento\RequisitionList\Controller\Item\Delete
     */
    protected $mock;

    /**
     * @var \Magento\Framework\App\Console\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requisitionList;

    /**
     * @var string
     */
    protected $mockClass;

    /**
     * Prepare requisition list
     */
    abstract protected function prepareRequisitionList();

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resultRedirect = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Redirect::class,
            ['setPath', 'setRefererUrl']
        );
        $this->request = $this->createMock(\Magento\Framework\App\Console\Request::class);
        $this->requestValidator = $this->createMock(\Magento\RequisitionList\Model\Action\RequestValidator::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->requisitionList = $this->createMock(\Magento\RequisitionList\Model\RequisitionList::class);
        $this->requisitionListRepository =
            $this->createMock(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class);
        $this->requisitionListItemRepository =
            $this->createMock(\Magento\RequisitionList\Model\RequisitionList\Items::class);
        $this->resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->mock = $objectManager->getObject(
            'Magento\RequisitionList\Controller\Item\\' . $this->mockClass,
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'requestValidator' => $this->requestValidator,
                'requisitionListRepository' => $this->requisitionListRepository,
                'logger' => $this->logger,
                'requisitionListItemRepository' => $this->requisitionListItemRepository
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $this->prepareRequest();
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn(null);
        $this->prepareResultRedirect();
        $this->prepareRequisitionList();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->mock->execute());
    }

    /**
     * Test execute method not allowed action
     */
    public function testExecuteWithNotAllowedAction()
    {
        $this->resultRedirect->expects($this->any())->method('setPath')->will($this->returnSelf());
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn($this->resultRedirect);
        $this->requisitionList->expects($this->never())->method('getItems');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->mock->execute());
    }

    /**
     * Test execute with Exception
     */
    public function testExecuteWithException()
    {
        $this->prepareRequest();
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn(null);
        $this->prepareResultRedirect();
        $this->prepareRequisitionList();
        $exception = new \Exception;
        $this->requisitionListRepository->expects($this->any())->method('save')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->mock->execute());
    }

    /**
     * Test execute with LocalizedException
     */
    public function testExecuteWithLocalizedException()
    {
        $this->prepareRequest();
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn(null);
        $this->prepareResultRedirect();
        $this->prepareRequisitionList();
        $phrase = new \Magento\Framework\Phrase('exception');
        $localizedException = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->requisitionListRepository->expects($this->any())->method('save')
            ->willThrowException($localizedException);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->mock->execute());
    }

    /**
     * Prepare request
     */
    protected function prepareRequest()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['requisition_id', null, 1],
                ['selected', null, '1, 2, 3, 4, 5'],
                ['qty', null, ['sku' => 1]]
            ]
        );
    }

    /**
     * Prepare result redirect
     */
    protected function prepareResultRedirect()
    {
        $this->resultRedirect->expects($this->any())->method('setPath')->willReturnSelf();
        $this->resultRedirect->expects($this->any())->method('setRefererUrl')->willReturnSelf();
        $this->resultFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);
    }
}
