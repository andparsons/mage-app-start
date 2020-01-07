<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Order\Info;

/**
 * Class QuoteTest
 */
class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Order\Info\Quote
     */
    private $quote;

    /**
     * @var int
     */
    private $quoteId;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->quoteId = 1;
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->order = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['getQuoteId']
        );
        $this->registry = $this->createPartialMock(
            \Magento\Framework\Registry::class,
            ['registry']
        );
        $this->registry->expects($this->any())->method('registry')->with('current_order')->willReturn($this->order);
        $this->order->expects($this->any())->method('getQuoteId')->will($this->returnValue($this->quoteId));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quote = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Order\Info\Quote::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'registry' => $this->registry,
                '_urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test getViewQuoteUrl
     *
     * @return void
     */
    public function testGetViewQuoteUrl()
    {
        $path = 'quotes/quote/view/';
        $url = 'http://example.com/';

        $this->urlBuilder->expects($this->any())
            ->method('getUrl')->will(
                $this->returnValue($url . $path . '/quote_id/' . $this->quoteId . '/')
            );

        $this->assertEquals($url . $path . '/quote_id/1/', $this->quote->getViewQuoteUrl());
    }

    /**
     * Test getViewQuoteLabel
     *
     * @return void
     */
    public function testGetViewQuoteLabel()
    {
        $this->assertEquals('#' . $this->order->getQuoteId() . ': ', $this->quote->getViewQuoteLabel());
    }

    /**
     * Test getQuoteName
     *
     * @return void
     */
    public function testGetQuoteName()
    {
        $quoteName = 'Test Quote';
        $quoteNegotiation = $this->getQuoteNegotiationMock();
        $quoteNegotiation->expects($this->exactly(2))->method('getQuoteName')->willReturn($quoteName);

        $this->assertSame($quoteName, $this->quote->getQuoteName());
    }

    /**
     * Test getQuoteName with exception
     *
     * @return void
     */
    public function testGetQuoteNameWithException()
    {
        $this->quoteRepository->expects($this->once())
            ->method('get')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertEquals(null, $this->quote->getQuoteName());
    }

    /**
     * Test isNegotiableQuote
     *
     * @param bool $expectedResult
     * @param int $quoteId
     * @dataProvider isNegotiableQuoteDataProvider
     */
    public function testIsNegotiableQuote($expectedResult, $quoteId)
    {
        $quoteNegotiation = $this->getQuoteNegotiationMock();
        $quoteNegotiation->expects($this->once())->method('getQuoteId')->willReturn($quoteId);

        $this->assertEquals($expectedResult, $this->quote->isNegotiableQuote());
    }

    /**
     * @return \Magento\NegotiableQuote\Model\NegotiableQuote
     */
    private function getQuoteNegotiationMock()
    {
        $cart = $this->createMock(
            \Magento\Quote\Api\Data\CartInterface::class
        );
        $cartExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getQuoteName', 'getNegotiableQuote']
        );
        $quoteNegotiation = $this->createMock(
            \Magento\NegotiableQuote\Model\NegotiableQuote::class
        );
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($cart);
        $cart->expects($this->once())->method('getExtensionAttributes')->willReturn($cartExtensionAttributes);
        $cartExtensionAttributes->expects($this->any())->method('getNegotiableQuote')->willReturn($quoteNegotiation);

        return $quoteNegotiation;
    }

    /**
     * @return array
     */
    public function isNegotiableQuoteDataProvider()
    {
        return [
            [true, 1],
            [false, null]
        ];
    }
}
