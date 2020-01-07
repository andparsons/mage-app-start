<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Order\Info;

/**
 * Test for \Magento\NegotiableQuote\Block\Order\Info\Quote class.
 */
class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Order\Info\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var int
     */
    protected $quoteId;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quoteId = 1;
        $this->registry = $this->createPartialMock(
            \Magento\Framework\Registry::class,
            ['registry']
        );
        $this->quoteMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getId', 'getStoreId']
        );
        $this->quoteMock->expects($this->any())->method('getId')->will($this->returnValue($this->quoteId));
        $this->quoteMock->expects($this->any())->method('getStoreId')->will($this->returnValue(0));
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore']
        );
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $storeMock->expects($this->any())->method('getCode')->will($this->returnValue(''));
        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->order = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['getQuoteId']
        );
        $this->order->expects($this->any())->method('getQuoteId')->will($this->returnValue($this->quoteId));
        $this->registry->expects($this->any())->method('registry')->with('current_order')->willReturn($this->order);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quote = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Order\Info\Quote::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'registry' => $this->registry,
                'quote' => $this->quoteMock,
                '_storeManager' => $this->storeManagerMock,
                '_urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test for getViewQuoteUrl.
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
     * Test for getViewQuoteLabel.
     *
     * @return void
     */
    public function testGetViewQuoteLabel()
    {
        $this->assertEquals('#' . $this->order->getQuoteId() . ': ', $this->quote->getViewQuoteLabel());
    }
}
