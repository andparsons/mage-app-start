<?php

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Invoice\View
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Invoice;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param bool $canReviewPayment
     * @param bool $canFetchUpdate
     * @param bool $expectedResult
     * @dataProvider isPaymentReviewDataProvider
     */
    public function testIsPaymentReview($canReviewPayment, $canFetchUpdate, $expectedResult)
    {
        // Create order mock
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->any())->method('canReviewPayment')->will($this->returnValue($canReviewPayment));
        $order->expects(
            $this->any()
        )->method(
            'canFetchPaymentReviewUpdate'
        )->will(
            $this->returnValue($canFetchUpdate)
        );

        // Create invoice mock
        $invoice = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Invoice::class
        )->disableOriginalConstructor()->setMethods(
            ['getOrder', '__wakeup']
        )->getMock();
        $invoice->expects($this->once())->method('getOrder')->will($this->returnValue($order));

        // Prepare block to test protected method
        $block = $this->getMockBuilder(
            \Magento\Sales\Block\Adminhtml\Order\Invoice\View::class
        )->disableOriginalConstructor()->setMethods(
            ['getInvoice']
        )->getMock();
        $block->expects($this->once())->method('getInvoice')->will($this->returnValue($invoice));
        $testMethod = new \ReflectionMethod(
            \Magento\Sales\Block\Adminhtml\Order\Invoice\View::class,
            '_isPaymentReview'
        );
        $testMethod->setAccessible(true);

        $this->assertEquals($expectedResult, $testMethod->invoke($block));
    }

    /**
     * @return array
     */
    public function isPaymentReviewDataProvider()
    {
        return [
            [true, true, true],
            [true, false, true],
            [false, true, true],
            [false, false, false]
        ];
    }
}
