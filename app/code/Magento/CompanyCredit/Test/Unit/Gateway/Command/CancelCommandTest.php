<?php
namespace Magento\CompanyCredit\Test\Unit\Gateway\Command;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for CancelCommand with order cancellation action, revert credit to company.
 */
class CancelCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var \Magento\Payment\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReader;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalance|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditBalance;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\CompanyCredit\Gateway\Command\CancelCommand
     */
    private $cancelCommand;

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
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new ObjectManagerHelper($this);
        $this->subjectReader = $this->objectManager->getObject(
            \Magento\Payment\Gateway\Helper\SubjectReader::class
        );
        $this->cancelCommand = $this->objectManager->getObject(
            \Magento\CompanyCredit\Gateway\Command\CancelCommand::class,
            [
                'creditBalance' => $this->creditBalance,
                'companyManagement' => $this->companyManagement,
                'subjectReader' => $this->subjectReader
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @param int $calls
     * @param int $companyId
     * @param bool $isCreditIncreased
     * @param \Magento\Framework\Phrase $message
     * @return void
     * @dataProvider testExecuteDataProvider
     */
    public function testExecute($calls, $companyId, $isCreditIncreased, $message)
    {
        $customerId = 1;
        $price = 33;
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatTxt'])
            ->getMock();
        $currency->expects($this->exactly($calls))
            ->method('formatTxt')
            ->willReturn((string)$price);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getBaseCurrency', 'getBaseGrandTotal', 'addStatusHistoryComment'])
            ->getMock();
        $order->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $order->expects($this->exactly($calls))
            ->method('getBaseGrandTotal')
            ->willReturn($price);
        $order->expects($this->exactly($calls))
            ->method('getBaseCurrency')
            ->willReturn($currency);
        $order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message, \Magento\Sales\Model\Order::STATE_CANCELED)
            ->willReturn(true);
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMockForAbstractClass();
        $paymentMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);
        $paymentDataObject = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentDataObject->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $companyMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $companyMock->expects($this->once())
            ->method('getId')
            ->willReturn($companyId);
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')
            ->with($customerId)
            ->willReturn($companyMock);
        $this->creditBalance->expects($this->exactly($calls))
            ->method('cancel')
            ->with($order)
            ->willReturn($isCreditIncreased);
        $subject = ['payment' => $paymentDataObject];

        $this->cancelCommand->execute($subject);
    }

    /**
     * Data provider for testExecute method.
     *
     * @return array
     */
    public function testExecuteDataProvider()
    {
        return [
            [
                'calls' => 1,
                'companyId' => 1,
                'creditBalance' => true,
                'message' => __('Order is canceled. We reverted %1 to the company credit.', 33),
            ],
            [
                'calls' => 1,
                'companyId' => 1,
                'creditBalance' => false,
                'message' => __('Order is canceled. The order amount is not reverted to the company credit.'),
            ],
            [
                'calls' => 0,
                'companyId' => 0,
                'creditBalance' => false,
                'message' => __(
                    'Order is cancelled. The order amount is not reverted to the company credit '
                    . 'because the company to which this customer belongs does not exist.'
                ),
            ],
        ];
    }

    /**
     * Test for execute method with exception.
     *
     * @return void
     * @expectedException \LogicException
     * @expectedExceptionMessage Order Payment should be provided
     */
    public function testExecuteWithException()
    {
        $paymentDataObject = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentDataObject->expects($this->once())
            ->method('getPayment')
            ->willReturn(false);
        $subject = ['payment' => $paymentDataObject];

        $this->cancelCommand->execute($subject);
    }
}
