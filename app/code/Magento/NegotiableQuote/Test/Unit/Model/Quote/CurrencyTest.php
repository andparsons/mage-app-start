<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Test for Magento\NegotiableQuote\Model\Quote\Currency class.
 */
class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteConverter;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Currency
     */
    private $currency;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteItemManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteConverter = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\NegotiableQuoteConverter::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->currency = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Quote\Currency::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'quoteItemManagement' => $this->quoteItemManagement,
                'negotiableQuoteConverter' => $this->negotiableQuoteConverter,
            ]
        );
    }

    /**
     * Test updateQuoteCurrency method.
     *
     * @param array $quoteData
     * @param array $snapshotData
     * @return void
     * @dataProvider updateQuoteCurrencyDataProvider
     */
    public function testUpdateQuoteCurrency(array $quoteData, array $snapshotData)
    {
        $quoteId = 1;
        $quoteCurrencyCode = 'USD';
        $baseCurrencyCode = 'EUR';
        $quoteCurrency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $baseCurrency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currentCurrency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository->expects($this->exactly(2))
            ->method('get')->withConsecutive([$quoteId, ['*']], [$quoteId])->willReturn($quote);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot', 'setSnapshot'])
            ->getMockForAbstractClass();
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrency', 'getCurrentCurrency'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())
            ->method('getStatus')->willReturn(NegotiableQuoteInterface::STATUS_CREATED);
        $quote->expects($this->atLeastOnce())->method('getCurrency')->willReturn($quoteCurrency);
        $quote->expects($this->exactly(2))->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($baseCurrency);
        $store->expects($this->once())->method('getCurrentCurrency')->willReturn($currentCurrency);
        $quoteCurrency->expects($this->atLeastOnce())->method('getQuoteCurrencyCode')->willReturn($quoteCurrencyCode);
        $currentCurrency->expects($this->once())->method('getCurrencyCode')->willReturn($quoteCurrencyCode);
        $quoteCurrency->expects($this->atLeastOnce())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $baseCurrency->expects($this->once())->method('getCurrencyCode')->willReturn($baseCurrencyCode);
        $quoteCurrency->expects($this->atLeastOnce())->method('getBaseToQuoteRate')->willReturn(0.7);
        $baseCurrency->expects($this->once())->method('getRate')->with($currentCurrency)->willReturn(0.8);
        $negotiableQuote->expects($this->atLeastOnce())->method('getSnapshot')->willReturn(json_encode($snapshotData));
        $this->negotiableQuoteConverter->expects($this->atLeastOnce())
            ->method('quoteToArray')->with($quote)->willReturn($quoteData);
        $this->currency->updateQuoteCurrency($quoteId);
    }

    /**
     * Data provider for updateQuoteCurrency method.
     *
     * @return array
     */
    public function updateQuoteCurrencyDataProvider()
    {
        $snapshotData = $quoteData = [
            'quote' => [
                'items_count' => 1,
                'items_qty' => 1,
                'base_grand_total' => 70,
                'base_subtotal' => 60,
                'base_subtotal_with_discount' => 60,
            ],
        ];
        $snapshotData['quote']['base_grand_total'] = 65;
        return [
            [$quoteData, $snapshotData],
            [$quoteData, $quoteData],
            [$quoteData, []],
        ];
    }
}
