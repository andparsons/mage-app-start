<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for QuoteTotals.
 */
class QuoteTotalsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorResultFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\QuoteTotals
     */
    private $quoteTotals;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->configHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->quoteTotals = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Validator\QuoteTotals::class,
            [
                'configHelper' => $this->configHelper,
                'validatorResultFactory' => $this->validatorResultFactory,
            ]
        );
    }

    /**
     * Test for validate().
     *
     * @param string $minimumAmountMessage
     * @param int $getMinimumAmountInvokesCount
     * @param int $getQuoteCurrencyCodeInvokesCount
     * @return void
     * @dataProvider validateDataProvider
     */
    public function testValidate(
        $minimumAmountMessage,
        $getMinimumAmountInvokesCount,
        $getQuoteCurrencyCodeInvokesCount
    ) {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->atLeastOnce())->method('getItemsCount')->willReturn(1);
        $this->configHelper->expects($this->atLeastOnce())->method('isQuoteAllowed')->with($quote)->willReturn(false);
        $this->configHelper->expects($this->atLeastOnce())->method('getMinimumAmountMessage')
            ->willReturn($minimumAmountMessage);
        $this->configHelper->expects($this->exactly($getMinimumAmountInvokesCount))->method('getMinimumAmount')
            ->willReturn(1.00);
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $currency->expects($this->exactly($getQuoteCurrencyCodeInvokesCount))->method('getQuoteCurrencyCode')
            ->willReturn('USD');
        $quote->expects($this->exactly($getQuoteCurrencyCodeInvokesCount))->method('getCurrency')
            ->willReturn($currency);
        $result->expects($this->atLeastOnce())->method('addMessage')->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->quoteTotals->validate(['quote' => $quote])
        );
    }

    /**
     * Test for validate() with empty quote data.
     *
     * @return void
     */
    public function testValidateWithEmptyQuote()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->never())->method('getItemsCount');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->quoteTotals->validate([])
        );
    }

    /**
     * Test for validate() for quote without quote items.
     *
     * @return void
     */
    public function testValidateWithEmptyQuoteItems()
    {
        $result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getItemsCount')->willReturn(0);
        $result->expects($this->atLeastOnce())->method('addMessage')->willReturnSelf();
        $this->configHelper->expects($this->never())->method('isQuoteAllowed');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->quoteTotals->validate(['quote' => $quote])
        );
    }

    /**
     * DataProvider for validate().
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            ['message', 0, 0],
            ['', 1, 1]
        ];
    }
}
