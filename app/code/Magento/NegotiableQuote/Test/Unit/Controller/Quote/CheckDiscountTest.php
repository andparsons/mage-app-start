<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Test for Magento\NegotiableQuote\Controller\Quote\CheckDiscount class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckDiscountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $json;

    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\CheckDiscount
     */
    private $checkDiscount;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->settingsProvider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\SettingsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGiftCards', 'getCouponCode'])
            ->getMockForAbstractClass();
        $this->json = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkDiscount = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\CheckDiscount::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'logger' => $this->logger,
                'settingsProvider' => $this->settingsProvider,
                'messageManager' => $this->messageManager,
                '_request' => $this->request,
                'serializer' => $this->serializer,
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testSuccessExecute()
    {
        $quoteId = 1;
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonError')
            ->willReturn($this->json);
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getGiftCards')->willReturn(null);
        $this->serializer->expects($this->never())
            ->method('unserialize');
        $this->quote->expects($this->once())->method('getCouponCode')->willReturn('132fdw43f234');
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonSuccess')
            ->with(['discount' => true])
            ->willReturn($this->json);

        $this->assertSame($this->json, $this->checkDiscount->execute());
    }

    /**
     * Test execute without quote id.
     *
     * @return void
     */
    public function testExecuteWithError()
    {
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn(false);
        $this->settingsProvider->expects($this->once())->method('retrieveJsonError')->willReturn($this->json);

        $this->assertSame($this->json, $this->checkDiscount->execute());
    }

    /**
     * Test execute without coupon and without gift card.
     *
     * @return void
     */
    public function testExecuteWithoutCoupon()
    {
        $quoteId = 1;
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonError')
            ->willReturn($this->json);
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getGiftCards')->willReturn(null);
        $this->quote->expects($this->once())->method('getCouponCode')->willReturn(null);

        $this->assertSame($this->json, $this->checkDiscount->execute());
    }

    /**
     * Test execute with gift card and without coupon.
     *
     * @return void
     */
    public function testExecuteWithoutCouponAndWithGiftCard()
    {
        $quoteId = 1;
        $giftCard = [
            'i' => '2',
            'c' => '0069H38J54IG',
            'a' => 10,
            'ba' => '10.0000',
        ];
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonError')
            ->willReturn($this->json);
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($this->quote);
        $this->quote->expects($this->any())
            ->method('getGiftCards')
            ->willReturn('[{"i":"2","c":"0069H38J54IG","a":10,"ba":"10.0000"}]');
        $this->serializer->expects($this->once())->method('unserialize')->willReturn($giftCard);
        $this->quote->expects($this->never())->method('getCouponCode');
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonSuccess')
            ->with(['discount' => true])
            ->willReturn($this->json);

        $this->assertSame($this->json, $this->checkDiscount->execute());
    }

    /**
     * Test execute with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $quoteId = 1;
        $exceptionMessage = 'No such entity with cartId = 1';
        $exception = new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonError')
            ->willReturn($this->json);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($exceptionMessage)
            ->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exception)->willReturnSelf();

        $this->assertSame($this->json, $this->checkDiscount->execute());
    }

    /**
     * Test execute with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $quoteId = 1;
        $exceptionMessage = 'An error occurred while quote creation.';
        $exception = new \Exception(__($exceptionMessage));
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonError')
            ->willReturn($this->json);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addException')
            ->with($exception, $exceptionMessage)
            ->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exception)->willReturnSelf();

        $this->assertSame($this->json, $this->checkDiscount->execute());
    }
}
