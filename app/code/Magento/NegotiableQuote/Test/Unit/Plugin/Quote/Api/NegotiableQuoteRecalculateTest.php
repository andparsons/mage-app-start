<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Api;

/**
 * Unit test for Magento\NegotiableQuote\Plugin\Quote\Api\NegotiableQuoteRecalculate class.
 */
class NegotiableQuoteRecalculateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteResource;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Api\NegotiableQuoteRecalculate
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
        $this->negotiableQuoteFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteResource = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(
            \Magento\Quote\Api\CartRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quotesForRecalculate = ["1" => true, "2" => false];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Api\NegotiableQuoteRecalculate::class,
            [
                'quoteItemManagement' => $this->quoteItemManagement,
                'negotiableQuoteFactory' => $this->negotiableQuoteFactory,
                'negotiableQuoteResource' => $this->negotiableQuoteResource,
                'quotesForRecalculate' => $quotesForRecalculate,
            ]
        );
    }

    /**
     * Test for beforeSave and afterSave methods.
     *
     * @param int $quoteId
     * @param string $newData
     * @param \PHPUnit\Framework\MockObject\Matcher\Invocation $recalculateExpected
     * @param string $recalculateMethod
     * @param int $price
     * @param \PHPUnit\Framework\MockObject\Matcher\Invocation $validateExpected
     * @return void
     * @dataProvider saveDataProvider
     */
    public function testSave(
        $quoteId,
        $newData,
        \PHPUnit\Framework\MockObject\Matcher\Invocation $recalculateExpected,
        $recalculateMethod,
        $price,
        \PHPUnit\Framework\MockObject\Matcher\Invocation $validateExpected
    ) {
        $subject = $this->quoteRepository;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($validateExpected)->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $extensionAttributes->expects($validateExpected)->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $negotiableQuote->expects($validateExpected)->method('hasData')->willReturn(true);
        $negotiableQuote->expects($validateExpected)->method('getData')->willReturn('data');
        $negotiableQuote->expects($recalculateExpected)->method('getNegotiatedPriceValue')->willReturn($price);

        $initialNegotiableQuote = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\NegotiableQuote::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteFactory->expects($validateExpected)->method('create')
            ->willReturn($initialNegotiableQuote);
        $initialNegotiableQuote->expects($validateExpected)->method('getQuoteId')->willReturn(3);
        $initialNegotiableQuote->expects($validateExpected)->method('getIsRegularQuote')->willReturn(true);
        $initialNegotiableQuote->expects($validateExpected)->method('getData')->willReturn($newData);

        $this->quoteItemManagement->expects($recalculateExpected)->method($recalculateMethod);

        $this->assertEquals([$quote], $this->plugin->beforeSave($subject, $quote));
        $this->plugin->afterSave($subject, null, $quote);
    }

    /**
     * Data provider for testSave method.
     *
     * @return array
     */
    public function saveDataProvider()
    {
        return [
            [3, 'new_data', $this->atLeastOnce(), 'updateQuoteItemsCustomPrices', 10, $this->atLeastOnce()],
            [3, 'new_data', $this->atLeastOnce(), 'recalculateOriginalPriceTax', null, $this->atLeastOnce()],
            [3, 'data', $this->never(), 'recalculateOriginalPriceTax', null, $this->atLeastOnce()],
            [2, 'data', $this->never(), 'recalculateOriginalPriceTax', null, $this->never()],
            [1, 'data', $this->atLeastOnce(), 'recalculateOriginalPriceTax', null, $this->atLeastOnce()],
        ];
    }
}
