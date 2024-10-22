<?php
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\CaptureDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\CaptureDataBuilder.
 */
class CaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptureDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new CaptureDataBuilder($this->subjectReaderMock);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Request\CaptureDataBuilder::build
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage No authorization transaction to proceed capture.
     */
    public function testBuildWithException()
    {
        $amount = 10.00;
        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => $amount,
        ];

        $this->paymentMock->expects(self::once())
            ->method('getCcTransId')
            ->willReturn('');

        $this->paymentDOMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);

        $this->builder->build($buildSubject);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Request\CaptureDataBuilder::build
     */
    public function testBuild()
    {
        $transactionId = 'b3b99d';
        $amount = 10.00;

        $expected = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => $amount,
        ];

        $this->paymentMock->expects(self::once())
            ->method('getCcTransId')
            ->willReturn($transactionId);

        $this->paymentDOMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        static::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
