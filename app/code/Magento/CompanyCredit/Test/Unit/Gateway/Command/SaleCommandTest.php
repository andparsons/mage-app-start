<?php
namespace Magento\CompanyCredit\Test\Unit\Gateway\Command;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for SaleCommand gateway model.
 */
class SaleCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\CompanyCredit\Gateway\Command\SaleCommand
     */
    private $saleCommand;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configInterfaceMock;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalance|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditBalanceMock;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagementMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->configInterfaceMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditBalanceMock = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditBalance::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyManagementMock = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $subjectReader = $this->objectManagerHelper->getObject(
            \Magento\Payment\Gateway\Helper\SubjectReader::class
        );
        $this->saleCommand = $this->objectManagerHelper->getObject(
            \Magento\CompanyCredit\Gateway\Command\SaleCommand::class,
            [
                'configInterface' => $this->configInterfaceMock,
                'creditBalance' => $this->creditBalanceMock,
                'companyManagement' => $this->companyManagementMock,
                'subjectReader' => $subjectReader
            ]
        );
    }

    /**
     * Test for execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $companyId = 1;
        $companyName = 'Company Name';
        $poNumber = '001';

        $paymentDataObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandSubject = ['payment' => $paymentDataObjectMock];
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setSkipOrderProcessing',
                'getOrder',
                'setAdditionalInformation',
                'getPoNumber'
            ])
            ->getMockForAbstractClass();
        $paymentDataObjectMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'setState'])
            ->getMock();
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $companyMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyManagementMock->expects($this->once())->method('getByCustomerId')->with($customerId)
            ->willReturn($companyMock);
        $this->configInterfaceMock->expects($this->once())->method('getValue')->with('order_status')
            ->willReturn('some_status');
        $orderMock->expects($this->once())->method('setState')->with(\Magento\Sales\Model\Order::STATE_NEW);
        $paymentMock->expects($this->once())->method('setSkipOrderProcessing')->with(true);
        $companyMock->expects($this->any())->method('getId')->willReturn($companyId);
        $companyMock->expects($this->any())->method('getCompanyName')->willReturn($companyName);
        $paymentMock->expects($this->exactly(2))->method('setAdditionalInformation')->withConsecutive(
            ['company_id', $companyId],
            ['company_name', $companyName]
        );
        $this->creditBalanceMock->expects($this->once())->method('decreaseBalanceByOrder')
            ->with($orderMock, $poNumber);
        $paymentMock->expects($this->once())->method('getPoNumber')->willReturn($poNumber);

        $this->saleCommand->execute($commandSubject);
    }

    /**
     * Test for execute() method with LogicException.
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

        $this->saleCommand->execute($commandSubject);
    }
}
