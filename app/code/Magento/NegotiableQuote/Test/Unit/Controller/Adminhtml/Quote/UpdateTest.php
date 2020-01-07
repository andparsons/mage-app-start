<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * Class UpdateTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteUpdater;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdatesInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteUpdatesInfo;

    /**
     * @var \Magento\AdvancedCheckout\Model\CartFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCurrency;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Update
     */
    private $update;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteUpdater = $this->createMock(\Magento\NegotiableQuote\Model\QuoteUpdater::class);
        $this->quoteUpdatesInfo = $this->createMock(\Magento\NegotiableQuote\Model\QuoteUpdatesInfo::class);
        $this->cartFactory = $this->createPartialMock(\Magento\AdvancedCheckout\Model\CartFactory::class, ['create']);
        $this->quoteCurrency = $this->createMock(\Magento\NegotiableQuote\Model\Quote\Currency::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->session = $this->createMock(\Magento\Backend\Model\Session::class);
        $this->response = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->response);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->update = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Update::class,
            [
                'quoteUpdater' => $this->quoteUpdater,
                'quoteUpdatesInfo' => $this->quoteUpdatesInfo,
                'cartFactory' => $this->cartFactory,
                'quoteCurrency' => $this->quoteCurrency,
                'quoteRepository' => $this->quoteRepository,
                'resultFactory' => $resultFactory,
                'messageManager' => $this->messageManager,
                '_request' => $this->request,
                '_session' => $this->session,
                'logger' => $this->logger,
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
        $updaterData = ['some_key' => 'Some Data'];
        $updaterMessages = ['Message #1'];
        $this->request->expects($this->at(0))->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->request->expects($this->at(1))->method('getParam')->with('quote')->willReturn($quoteData);
        $this->request->expects($this->at(2))->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->quoteCurrency->expects($this->once())->method('updateQuoteCurrency')->with($quoteId);
        $this->quoteUpdater->expects($this->once())
            ->method('updateQuote')->with($quoteId, $quoteData + ['items' => []], false)->willReturn(true);
        $quote = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
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
        $quote->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())->method('getNegotiableQuote')->willReturn($quoteNegotiation);
        $quoteNegotiation->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId, ['*'])->willReturn($quote);
        $this->quoteUpdatesInfo->expects($this->once())
            ->method('getQuoteUpdatedData')->with($quote, $quoteData + ['items' => []])->willReturn($updaterData);
        $cart = $this->createMock(\Magento\AdvancedCheckout\Model\Cart::class);
        $this->cartFactory->expects($this->once())->method('create')->willReturn($cart);
        $cart->expects($this->once())->method('setSession')->with($this->session)->willReturnSelf();
        $cart->expects($this->once())->method('getFailedItems')->willReturn([]);
        $this->quoteUpdater->expects($this->once())->method('getMessages')->willReturn($updaterMessages);
        $this->response->expects($this->once())->method('setJsonData')->with(
            json_encode(
                $updaterData + [
                    'hasFailedItems' => false,
                    'messages' => $updaterMessages,
                ],
                JSON_NUMERIC_CHECK
            )
        )->willReturnSelf();
        $this->assertEquals($this->response, $this->update->execute());
    }

    /**
     * Test for method execute without quote.
     *
     * @return void
     */
    public function testExecuteWithoutQuote()
    {
        $quoteId = 1;
        $quoteData = [];
        $this->request->expects($this->at(0))->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->request->expects($this->at(1))->method('getParam')->with('quote')->willReturn($quoteData);
        $this->request->expects($this->at(2))->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->quoteCurrency->expects($this->once())->method('updateQuoteCurrency')->with($quoteId);
        $this->quoteUpdater->expects($this->once())
            ->method('updateQuote')->with($quoteId, $quoteData + ['items' => []], false)->willReturn(true);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId, ['*'])
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->response->expects($this->once())->method('setJsonData')->with(
            json_encode(
                [
                    'messages' => [['type' => 'error', 'text' => __('Requested quote was not found.')]],
                ],
                JSON_NUMERIC_CHECK
            )
        )->willReturnSelf();
        $this->assertEquals($this->response, $this->update->execute());
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $quoteId = 1;
        $quoteData = [];
        $updaterData = ['some_key' => 'Some Data'];
        $updaterMessages = ['Error Message'];
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->request->expects($this->at(0))->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->request->expects($this->at(1))->method('getParam')->with('quote')->willReturn($quoteData);
        $this->request->expects($this->at(2))->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->quoteCurrency->expects($this->once())->method('updateQuoteCurrency')->with($quoteId);
        $this->quoteUpdater->expects($this->once())->method('updateQuote')
            ->with($quoteId, $quoteData + ['items' => []], false)->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')->with(__('Exception occurred during update quote'))->willReturnSelf();
        $quote = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
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
        $quote->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())->method('getNegotiableQuote')->willReturn($quoteNegotiation);
        $quoteNegotiation->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId, ['*'])->willReturn($quote);
        $this->quoteUpdatesInfo->expects($this->once())
            ->method('getQuoteUpdatedData')->with($quote, $quoteData + ['items' => []])->willReturn($updaterData);
        $cart = $this->createMock(\Magento\AdvancedCheckout\Model\Cart::class);
        $this->cartFactory->expects($this->once())->method('create')->willReturn($cart);
        $cart->expects($this->once())->method('setSession')->with($this->session)->willReturnSelf();
        $cart->expects($this->once())->method('getFailedItems')->willReturn([]);
        $this->quoteUpdater->expects($this->once())->method('getMessages')->willReturn($updaterMessages);
        $this->response->expects($this->once())->method('setJsonData')->with(
            json_encode(
                $updaterData + [
                    'hasFailedItems' => false,
                    'messages' => $updaterMessages,
                ],
                JSON_NUMERIC_CHECK
            )
        )->willReturnSelf();
        $this->assertEquals($this->response, $this->update->execute());
    }
}
