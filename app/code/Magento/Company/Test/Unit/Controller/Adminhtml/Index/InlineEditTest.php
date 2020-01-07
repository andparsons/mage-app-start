<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEditTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Company\Controller\Adminhtml\Index\InlineEdit */
    protected $controller;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit\Framework\MockObject_MockObject */
    protected $companyData;

    /** @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit\Framework\MockObject_MockObject */
    protected $resultJsonFactory;

    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit\Framework\MockObject_MockObject */
    protected $resultJson;

    /** @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit\Framework\MockObject_MockObject */
    protected $companyRepository;

    /** @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit\Framework\MockObject_MockObject */
    protected $dataObjectHelper;

    /** @var \Magento\Framework\Message\Collection|\PHPUnit\Framework\MockObject_MockObject */
    protected $messageCollection;

    /** @var \Magento\Framework\Message\MessageInterface|\PHPUnit\Framework\MockObject_MockObject */
    protected $message;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject_MockObject */
    protected $logger;

    /** @var array */
    protected $items;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false);
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->companyData = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\CompanyInterface::class,
            [],
            '',
            false
        );
        $this->resultJsonFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create']
        );
        $this->resultJson = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->companyRepository = $this->getMockForAbstractClass(
            \Magento\Company\Api\CompanyRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->messageCollection = $this->createMock(\Magento\Framework\Message\Collection::class);
        $this->message = $this->getMockForAbstractClass(
            \Magento\Framework\Message\MessageInterface::class,
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $this->controller = $objectManager->getObject(
            \Magento\Company\Controller\Adminhtml\Index\InlineEdit::class,
            [
                'companyRepository' => $this->companyRepository,
                'resultJsonFactory' => $this->resultJsonFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
                'logger' => $this->logger,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
            ]
        );

        $this->items = [
            14 => [
                'email' => 'test@test.ua',
            ]
        ];
    }

    /**
     * Prepare mocks for testing
     *
     * @return void
     */
    protected function prepareMocksForTesting()
    {
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('items', [])
            ->willReturn($this->items);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->companyRepository->expects($this->once())
            ->method('get')
            ->with(14)
            ->willReturn($this->companyData);
        $this->dataObjectHelper->expects($this->any())
            ->method('populateWithArray')
            ->with(
                $this->companyData,
                [
                    'email' => 'test@test.ua',
                ],
                \Magento\Company\Api\Data\CompanyInterface::class
            );
        $this->companyData->expects($this->any())
            ->method('getId')
            ->willReturn(12);
    }

    /**
     * Prepare mocks for error messages processing
     *
     * @return void
     */
    protected function prepareMocksForErrorMessagesProcessing()
    {
        $this->messageManager->expects($this->atLeastOnce())
            ->method('getMessages')
            ->willReturn($this->messageCollection);
        $this->messageCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->message]);
        $this->messageCollection->expects($this->once())
            ->method('getCount')
            ->willReturn(1);
        $this->message->expects($this->once())
            ->method('getText')
            ->willReturn('Error text');
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => ['Error text'],
                    'error' => true,
                ]
            )
            ->willReturnSelf();
    }

    /**
     * Test for method execute
     *
     * @return void
     */
    public function testExecute()
    {
        $this->prepareMocksForTesting(1);
        $this->companyRepository->expects($this->once())
            ->method('save')
            ->with($this->companyData);
        $this->prepareMocksForErrorMessagesProcessing();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Test for method execute without items
     *
     * @return void
     */
    public function testExecuteWithoutItems()
    {
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('items', [])
            ->willReturn([]);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(false);
        $this->resultJson
            ->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error' => true,
                ]
            )
            ->willReturnSelf();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Test for method execute with localized exception
     *
     * @return void
     */
    public function testExecuteLocalizedException()
    {
        $exception = new \Magento\Framework\Exception\LocalizedException(__('Exception message'));
        $this->prepareMocksForTesting();
        $this->companyRepository->expects($this->once())
            ->method('save')
            ->with($this->companyData)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('[Company ID: 12] can not be saved');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->prepareMocksForErrorMessagesProcessing();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Test for method execute with input exception
     *
     * @return void
     */
    public function testExecuteInputException()
    {
        $exception = new \Magento\Framework\Exception\InputException(__('Exception message'));
        $this->prepareMocksForTesting();
        $this->companyRepository->expects($this->once())
            ->method('save')
            ->with($this->companyData)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('[Company ID: 12] can not be saved');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->prepareMocksForErrorMessagesProcessing();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Test for method execute with exception
     *
     * @return void
     */
    public function testExecuteException()
    {
        $exception = new \Exception('Exception message');
        $this->prepareMocksForTesting();
        $this->companyRepository->expects($this->once())
            ->method('save')
            ->with($this->companyData)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('[Company ID: 12] can not be saved');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->prepareMocksForErrorMessagesProcessing();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }
}
