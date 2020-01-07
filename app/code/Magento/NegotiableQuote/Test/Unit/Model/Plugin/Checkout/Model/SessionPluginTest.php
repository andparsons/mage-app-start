<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Checkout\Model;

/**
 * Unit test for Magento\NegotiableQuote\Model\Plugin\Checkout\Model\SessionPlugin class.
 */
class SessionPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Checkout\Model\SessionPlugin
     */
    private $plugin;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->restriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\Checkout\Model\SessionPlugin::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $this->restriction,
                'request' => $this->request
            ]
        );
    }

    /**
     * Test afterGetQuoteId method when quote is negotiable one.
     *
     * @return void
     */
    public function testAfterGetQuoteIdIsNegotiableQuote()
    {
        $id = 2;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('negotiableQuoteId')
            ->willReturn($id);
        $this->quoteRepository->expects($this->once())->method('get')->with($id, ['*'])->willReturn($quote);
        $this->restriction->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->restriction->expects($this->once())->method('canProceedToCheckout')->willReturn(true);
        $quote->expects($this->once())->method('setIsActive')->with(true)->willReturnSelf();

        $this->assertEquals(2, $this->plugin->afterGetQuoteId($subject, 1));
    }

    /**
     * Test afterGetQuoteId method when quote is regular one.
     *
     * @return void
     */
    public function testAfterGetQuoteIdIsRegularQuote()
    {
        $id = null;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('negotiableQuoteId')
            ->willReturn($id);
        $this->quoteRepository->expects($this->once())->method('get')->with(1, ['*'])->willReturn($quote);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);

        $this->assertNull($this->plugin->afterGetQuoteId($subject, 1));
    }

    /**
     * Test afterGetQuoteId method throws exception.
     *
     * @return void
     */
    public function testAfterGetQuoteIdWithException()
    {
        $id = null;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $subject = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('negotiableQuoteId')
            ->willReturn($id);
        $this->quoteRepository->expects($this->once())->method('get')->with(1, ['*'])->willThrowException($exception);

        $this->assertEquals(1, $this->plugin->afterGetQuoteId($subject, 1));
    }
}
