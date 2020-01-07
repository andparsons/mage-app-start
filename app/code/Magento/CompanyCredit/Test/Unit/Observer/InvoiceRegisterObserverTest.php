<?php

namespace Magento\CompanyCredit\Test\Unit\Observer;

/**
 *  Unit test for observer registration invoice.
 */
class InvoiceRegisterObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Observer\InvoiceRegisterObserver
     */
    private $invoiceRegisterObserver;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoice;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $observer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->observer = $this->createPartialMock(
            \Magento\Framework\Event\Observer::class,
            ['getOrder', 'getInvoice']
        );

        $this->invoice = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $this->order = $this->createMock(\Magento\Sales\Model\Order::class);
        $payment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $currency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $currency->expects($this->once())->method('formatTxt')->willReturnArgument(0);
        $this->order->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $this->order->expects($this->once())->method('getPayment')->willReturn($payment);
        $payment->expects($this->once())->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);

        $this->observer->expects($this->once())->method('getOrder')->willReturn($this->order);
        $this->observer->expects($this->once())->method('getInvoice')->willReturn($this->invoice);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->invoiceRegisterObserver = $objectManager->getObject(
            \Magento\CompanyCredit\Observer\InvoiceRegisterObserver::class
        );
    }

    /**
     * Test method for execute.
     *
     * @return void
     */
    public function testExecuteWithStatus()
    {
        $this->order->expects($this->once())->method('getStatus')->willReturn('new');
        $this->order->expects($this->once())->method('addStatusHistoryComment');
        $this->order->expects($this->never())->method('setCustomerNote');

        $this->invoiceRegisterObserver->execute($this->observer);
    }

    /**
     * Test method for execute.
     *
     * @return void
     */
    public function testExecuteWithoutStatus()
    {
        $this->order->expects($this->once())->method('getStatus')->willReturn(null);
        $this->order->expects($this->never())->method('addStatusHistoryComment');
        $this->order->expects($this->once())->method('setCustomerNote');

        $this->invoiceRegisterObserver->execute($this->observer);
    }
}
