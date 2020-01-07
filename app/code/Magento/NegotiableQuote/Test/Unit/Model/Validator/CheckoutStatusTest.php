<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Validator;

/**
 * Unit test for CheckoutStatus.
 */
class CheckoutStatusTest extends AbstractStatusTest
{
    /**
     * @var string
     */
    protected $statusClass = \Magento\NegotiableQuote\Model\Validator\CheckoutStatus::class;

    /**
     * Test for validate().
     *
     * @return void
     */
    public function testValidate()
    {
        $this->prepareMocksForValidate();
        $this->restriction->expects($this->atLeastOnce())->method('canProceedToCheckout')->willReturn(false);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->status->validate($this->data)
        );
    }

    /**
     * Test validate() with empty quote data.
     *
     * @return void
     */
    public function testValidateWithEmptyQuoteData()
    {
        $this->prepareMocksForValidateWithEmptyQuoteData();
        $this->restriction->expects($this->never())->method('canProceedToCheckout');

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Validator\ValidatorResult::class,
            $this->status->validate($this->data)
        );
    }
}
