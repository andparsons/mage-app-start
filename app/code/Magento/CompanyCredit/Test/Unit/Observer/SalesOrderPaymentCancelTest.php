<?php
namespace Magento\CompanyCredit\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for \Magento\CompanyCredit\Observer\SalesOrderPaymentCancel.
 */
class SalesOrderPaymentCancelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\CompanyCredit\Observer\SalesOrderPaymentCancel
     */
    private $salesOrderPaymentCancel;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->salesOrderPaymentCancel = $this->objectManagerHelper->getObject(
            \Magento\CompanyCredit\Observer\SalesOrderPaymentCancel::class
        );
    }

    /**
     * Test execute with any other payments.
     *
     * @return void
     */
    public function testExecuteWithOtherPayments()
    {
        $paymentMethodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $observerMock = $this->buildObserverMock($paymentMethodInstanceMock);

        $paymentMethodInstanceMock->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('another_payment_method');

        $paymentMethodInstanceMock->expects($this->never())
            ->method('cancel');

        $this->salesOrderPaymentCancel->execute($observerMock);
    }

    /**
     * Test execute with company payments.
     *
     * @return void
     */
    public function testExecute()
    {
        $paymentMethodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $observerMock = $this->buildObserverMock($paymentMethodInstanceMock);

        $paymentMethodInstanceMock->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);

        $paymentMethodInstanceMock->expects($this->once())
            ->method('cancel');

        $this->salesOrderPaymentCancel->execute($observerMock);
    }

    /**
     * Build observer mock.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $paymentMethodInstanceMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildObserverMock(\PHPUnit_Framework_MockObject_MockObject $paymentMethodInstanceMock)
    {
        $paymentMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $paymentMock->expects($this->atLeastOnce())
            ->method('getMethodInstance')
            ->willReturn($paymentMethodInstanceMock);

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($paymentMock);

        return $observerMock;
    }
}
