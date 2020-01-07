<?php
namespace Magento\CompanyCredit\Test\Unit\Gateway\Command;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for refund command gateway command.
 */
class RefundCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\CompanyCredit\Gateway\Command\RefundCommand
     */
    private $refundCommand;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalance|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditBalance;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->creditBalance = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditBalance::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $subjectReader = $this->objectManagerHelper->getObject(
            \Magento\Payment\Gateway\Helper\SubjectReader::class
        );
        $this->refundCommand = $this->objectManagerHelper->getObject(
            \Magento\CompanyCredit\Gateway\Command\RefundCommand::class,
            [
                'creditBalance' => $this->creditBalance,
                'subjectReader' => $subjectReader
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $paymentDataObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandSubject = ['payment' => $paymentDataObjectMock];
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getOrder',
                'getCreditmemo',
            ])
            ->getMockForAbstractClass();
        $paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'setState'])
            ->getMock();
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $creditmemo = $this->getMockBuilder(\Magento\Sales\Api\Data\CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getCreditmemo')->willReturn($creditmemo);

        $this->creditBalance->expects($this->once())->method('refund')
            ->with($orderMock, $creditmemo);

        $this->refundCommand->execute($commandSubject);
    }

    /**
     * Test for execute method with LogicException.
     *
     * @return void
     * @expectedException \LogicException
     * @expectedExceptionMessage Order Payment should be provided
     */
    public function testExecuteWithLogicException()
    {
        $paymentDataObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandSubject = ['payment' => $paymentDataObjectMock];
        $paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn([]);

        $this->refundCommand->execute($commandSubject);
    }
}
