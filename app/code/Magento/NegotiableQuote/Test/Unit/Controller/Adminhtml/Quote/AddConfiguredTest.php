<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * Class AddConfiguredTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddConfiguredTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\NegotiableQuote\Controller\Adminhtml\Quote\AddConfigured
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCurrency;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteUpdater;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\AdvancedCheckout\Model\CartFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            ['getParam'],
            '',
            false,
            false,
            true,
            []
        );
        $this->quoteCurrency = $this->createMock(\Magento\NegotiableQuote\Model\Quote\Currency::class);
        $this->quoteUpdater = $this->createMock(\Magento\NegotiableQuote\Model\QuoteUpdater::class);
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $this->response = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->response);
        $this->quoteRepository = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->quote = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['collectTotals', 'setSession', 'getFailedItems']
        );
        $this->quote->expects($this->once())->method('setSession')->willReturnSelf();
        $this->cartFactory = $this->createPartialMock(\Magento\AdvancedCheckout\Model\CartFactory::class, ['create']);
        $this->cartFactory->expects($this->once())->method('create')->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getFailedItems')->willReturn([]);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\AddConfigured::class,
            [
                'logger' => $this->logger,
                'quoteRepository' => $this->quoteRepository,
                'messageManager' => $this->messageManager,
                'resultFactory' => $resultFactory,
                'quoteCurrency' => $this->quoteCurrency,
                'quoteUpdater' => $this->quoteUpdater,
                'cartFactory' => $this->cartFactory,
                '_request' => $this->request,
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
        $quoteId = 1;
        $quoteData = [];
        $itemId = 2;
        $updaterMessages = ['Message #1'];
        $configuredItems = [$itemId => 'config_value'];
        $addBySku = [$itemId => []];
        $this->request->expects($this->at(0))->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->request->expects($this->at(1))->method('getParam')->with('dataSend')->willReturn(json_encode(null));
        $this->request->expects($this->at(2))->method('getParam')->with('add_by_sku')->willReturn($addBySku);
        $this->request->expects($this->at(3))->method('getParam')->with('item')->willReturn($configuredItems);
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($this->quote);
        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quoteNegotiation->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $this->quote->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())->method('getNegotiableQuote')->willReturn($quoteNegotiation);
        $this->quoteCurrency->expects($this->once())->method('updateQuoteCurrency')->with($quoteId);
        $this->quoteUpdater->expects($this->once())->method('updateQuote')
            ->with(
                $quoteId,
                $quoteData + [
                    'configuredItems' => [
                        $itemId => ['config' => $configuredItems[$itemId]]
                    ]
                ]
            )->willReturn(true);
        $this->quoteUpdater->expects($this->once())->method('getMessages')->willReturn($updaterMessages);
        $this->response->expects($this->once())->method('setJsonData')
            ->with(
                json_encode(
                    [
                        'hasFailedItems' => false,
                        'messages' => $updaterMessages,
                    ],
                    JSON_NUMERIC_CHECK
                )
            )->willReturnSelf();
        $this->assertEquals($this->response, $this->controller->execute());
    }

    /**
     * Test for method execute throwing exception.
     *
     * @return void
     */
    public function testExecuteException()
    {
        $updaterMessages = ['Error Message'];
        $this->request->expects($this->at(0))->method('getParam')->with('quote_id')->willReturn(null);
        $this->request->expects($this->at(1))->method('getParam')->with('dataSend')->willReturn(json_encode(null));
        $this->request->expects($this->at(2))->method('getParam')->with('add_by_sku')->willReturn([]);
        $this->request->expects($this->at(3))->method('getParam')->with('item')->willReturn([]);
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($this->quote);
        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quoteNegotiation->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $this->quote->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())->method('getNegotiableQuote')->willReturn($quoteNegotiation);
        $exception = new \Exception('Exception message');
        $this->quoteUpdater->expects($this->once())->method('updateQuote')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->messageManager->expects($this->once())->method('addError')->with('Something went wrong');
        $this->quoteUpdater->expects($this->once())->method('getMessages')->willReturn($updaterMessages);
        $this->response->expects($this->once())->method('setJsonData')
            ->with(
                json_encode(
                    [
                        'hasFailedItems' => false,
                        'messages' => $updaterMessages,
                    ],
                    JSON_NUMERIC_CHECK
                )
            )->willReturnSelf();
        $this->assertEquals($this->response, $this->controller->execute());
    }
}
