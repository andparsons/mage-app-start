<?php

namespace Magento\Company\Test\Unit\Controller\Team;

/**
 * Test Magento\Company\Controller\Team\Delete class.
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Team\Delete
     */
    private $delete;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Company\Api\TeamRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $teamRepository;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->structureManager = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->teamRepository = $this->getMockBuilder(\Magento\Company\Api\TeamRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyContext = $this->getMockBuilder(\Magento\Company\Model\CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->delete = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Team\Delete::class,
            [
                'resultFactory' => $this->resultFactory,
                'structureManager' => $this->structureManager,
                'teamRepository' => $this->teamRepository,
                'logger' => $this->logger,
                '_request' => $this->request,
                'companyContext' => $this->companyContext,
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $teamId = 1;
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['teams' => [1, 5, 6]]);
        $this->request->expects($this->once())->method('getParam')->with('team_id')->willReturn($teamId);
        $this->teamRepository->expects($this->once())->method('deleteById')->with($teamId);
        $this->resultJson->expects($this->once())->method('setData')->with(
            [
                'status' => 'ok',
                'message' => __('The team was successfully deleted.'),
                'data' => [],
            ]
        )->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);

        $this->assertEquals($this->resultJson, $this->delete->execute());
    }

    /**
     * Test execute method with invalid team id.
     *
     * @return void
     */
    public function testExecuteWithInvalidTeamId()
    {
        $customerId = 1;
        $teamId = 1;
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['teams' => [5, 6]]);
        $this->request->expects($this->once())->method('getParam')->with('team_id')->willReturn($teamId);
        $this->resultJson->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'message' => __('You are not allowed to do this.'),
                'payload' => [],
            ]
        )->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);

        $this->assertEquals($this->resultJson, $this->delete->execute());
    }

    /**
     * Test execute method with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $customerId = 1;
        $teamId = 1;
        $exception = new \Magento\Framework\Exception\LocalizedException(__('Localized Exception.'));
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['teams' => [1, 5, 6]]);
        $this->request->expects($this->once())->method('getParam')->with('team_id')->willReturn($teamId);
        $this->teamRepository->expects($this->once())->method('deleteById')->willThrowException($exception);
        $this->resultJson->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'message' => __('Localized Exception.'),
                'payload' => [],
            ]
        )->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);

        $this->assertEquals($this->resultJson, $this->delete->execute());
    }

    /**
     * Test execute method with generic exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $customerId = 1;
        $teamId = 1;
        $exception = new \Exception();
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($customerId)
            ->willReturn(['teams' => [1, 5, 6]]);
        $this->request->expects($this->once())->method('getParam')->with('team_id')->willReturn($teamId);
        $this->teamRepository->expects($this->once())->method('deleteById')->willThrowException($exception);
        $this->resultJson->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'message' => __('Something went wrong.'),
                'payload' => [],
            ]
        )->willReturnSelf();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals($this->resultJson, $this->delete->execute());
    }
}
