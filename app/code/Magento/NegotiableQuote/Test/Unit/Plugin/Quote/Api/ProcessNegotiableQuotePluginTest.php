<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Api;

/**
 * Unit test for Magento\NegotiableQuote\Plugin\Quote\Api\ProcessNegotiableQuotePlugin class.
 */
class ProcessNegotiableQuotePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteConverter;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Api\ProcessNegotiableQuotePlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quoteItemManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->restriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteConverter = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\NegotiableQuoteConverter::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(
            \Magento\Framework\Serialize\SerializerInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Api\ProcessNegotiableQuotePlugin::class,
            [
                'quoteItemManagement' => $this->quoteItemManagement,
                'restriction' => $this->restriction,
                'negotiableQuoteConverter' => $this->negotiableQuoteConverter,
                'serializer' => $this->serializer,
            ]
        );
    }

    /**
     * Test for afterGet method.
     *
     * @param bool $quoteCanBeSubmitted
     * @param float|null $negotiatedPrice
     * @return void
     * @dataProvider afterGetDataProvider
     */
    public function testAfterGet($quoteCanBeSubmitted, $negotiatedPrice)
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        if ($quoteCanBeSubmitted) {
            $quote = $this->processQuoteIfCanSubmit($result);
        } else {
            $quote = $this->processQuoteIfCantSubmit($result, $negotiatedPrice);
        }

        $this->assertEquals($quote, $this->plugin->afterGet($subject, $result));

        // test result caching
        $this->negotiableQuoteConverter->expects($this->never())
            ->method('arrayToQuote');
        $this->assertEquals($quote, $this->plugin->afterGet($subject, $result));
    }

    /**
     * Test for afterGetList method.
     *
     * @return void
     */
    public function testAfterGetList()
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $item = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result->expects($this->once())->method('getItems')->willReturn([$item]);
        $quote = $this->processQuoteIfCanSubmit($item);
        $result->expects($this->once())->method('setItems')->with([$quote])->willReturnSelf();

        $this->assertEquals($result, $this->plugin->afterGetList($subject, $result));
    }

    /**
     * Process quote if it can't be submitted.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $quote
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function processQuoteIfCanSubmit(\PHPUnit_Framework_MockObject_MockObject $quote)
    {
        $quoteId = 1;
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $this->restriction->expects($this->atLeastOnce())->method('setQuote')->with($quote)->willReturnSelf();
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $this->restriction->expects($this->atLeastOnce())->method('canSubmit')->willReturn(false);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getSnapshot')
            ->willReturn('serialized data');
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->willReturn(['quote_snapshot' => 'quote_data']);
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('arrayToQuote')
            ->with(['quote_snapshot' => 'quote_data'])
            ->willReturn($quote);
        return $quote;
    }

    /**
     * Process quote if it can be submitted.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $quote
     * @param float|null $negotiatedPrice
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function processQuoteIfCantSubmit(\PHPUnit_Framework_MockObject_MockObject $quote, $negotiatedPrice)
    {
        $quoteId = 1;
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $this->restriction->expects($this->atLeastOnce())->method('setQuote')->with($quote)->willReturnSelf();
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $this->restriction->expects($this->atLeastOnce())->method('canSubmit')->willReturn(true);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getNegotiatedPriceValue')
            ->willReturn($negotiatedPrice);
        if ($negotiatedPrice) {
            $this->quoteItemManagement->expects($this->once())
                ->method('updateQuoteItemsCustomPrices')
                ->with($quoteId, false)
                ->willReturn(true);
        } else {
            $this->quoteItemManagement->expects($this->once())
                ->method('recalculateOriginalPriceTax')
                ->with($quoteId, true, true, false, false)
                ->willReturn(true);
        }

        return $quote;
    }

    /**
     * Data provider for afterGet method.
     *
     * @return array
     */
    public function afterGetDataProvider()
    {
        return [
            [true, null],
            [false, null],
            [false, 100.5]
        ];
    }
}
