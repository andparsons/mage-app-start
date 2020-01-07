<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Sales\Model\Order;

/**
 * Unit test for validator invoice quantity.
 */
class InvoiceQuantityValidatorPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Plugin\Sales\Model\Order\InvoiceQuantityValidatorPlugin
     */
    private $invoiceQuantityValidatorPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->invoiceQuantityValidatorPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Sales\Model\Order\InvoiceQuantityValidatorPlugin::class
        );
    }

    /**
     * Test for aroundValidate method.
     *
     * @param int $itemQty
     * @param int $expect
     * @return void
     * @dataProvider aroundValidateDataProvider
     */
    public function testAroundValidate($itemQty, $expect)
    {
        $subject = $this->createMock(
            \Magento\Sales\Model\Order\InvoiceQuantityValidator::class
        );
        $method = function ($invoice) {
            return [];
        };
        $invoice = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $invoice->expects($this->atLeastOnce())->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);

        $item = $this->createMock(
            \Magento\Sales\Model\Order\Item::class
        );
        $item->expects($this->once())->method('getItemId')->willReturn(1);
        $item->expects($this->once())->method('getQtyOrdered')->willReturn(2);
        $order->expects($this->once())->method('getItems')->willReturn([$item]);

        $itemInvoice = $this->createMock(\Magento\Sales\Api\Data\InvoiceItemInterface::class);
        $itemInvoice->expects($this->atLeastOnce())->method('getOrderItemId')->willReturn(1);
        $itemInvoice->expects($this->once())->method('getQty')->willReturn($itemQty);
        $invoice->expects($this->once())->method('getItems')->willReturn([$itemInvoice]);

        $result = $this->invoiceQuantityValidatorPlugin->aroundValidate($subject, $method, $invoice);
        $this->assertEquals($expect, count($result));
    }

    /**
     * Data provider for test.
     *
     * @return array
     */
    public function aroundValidateDataProvider()
    {
        return [[2, 0], [1, 1]];
    }
}
