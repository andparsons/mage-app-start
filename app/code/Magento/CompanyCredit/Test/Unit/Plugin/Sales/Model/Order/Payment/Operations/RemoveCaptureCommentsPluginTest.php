<?php
namespace Magento\CompanyCredit\Test\Unit\Plugin\Sales\Model\Order\Payment\Operations;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CompanyCredit\Plugin\Sales\Model\Order\Payment\Operations\RemoveCaptureCommentsPlugin;
use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;
use Magento\Sales\Model\Order\Payment\Operations\CaptureOperation;

/**
 * Unit tests for RemoveCaptureCommentsPlugin.
 */
class RemoveCaptureCommentsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CaptureOperation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var RemoveCaptureCommentsPlugin
     */
    private $removeCaptureCommentsPlugin;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->subjectMock = $this->getMockBuilder(CaptureOperation::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->removeCaptureCommentsPlugin = $this->objectManagerHelper->getObject(
            \Magento\CompanyCredit\Plugin\Sales\Model\Order\Payment\Operations\RemoveCaptureCommentsPlugin::class,
            []
        );
    }

    /**
     * Test for aroundCapture() method.
     *
     * @return void
     */
    public function testAroundCapture()
    {
        $invoice = null;
        $statusHistories = null;

        $this->paymentMock->expects($this->atLeastOnce())->method('getMethod')
            ->willReturn(CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->atLeastOnce())->method('getStatusHistories')->willReturn($statusHistories);
        $orderMock->expects($this->atLeastOnce())->method('setStatusHistories')->with($statusHistories)
            ->willReturnSelf();

        $closure = function ($paymentMock) {
            return $paymentMock;
        };

        $this->assertEquals(
            $this->paymentMock,
            $this->removeCaptureCommentsPlugin->aroundCapture(
                $this->subjectMock,
                $closure,
                $this->paymentMock,
                $invoice
            )
        );
    }

    /**
     * Test for aroundCapture() method if payment method is not 'companycredit'.
     *
     * @return void
     */
    public function testAroundCaptureIfMethodNotCompanyCredit()
    {
        $invoice = null;
        $this->paymentMock->expects($this->atLeastOnce())->method('getMethod')->willReturn('dummy method');
        $this->paymentMock->expects($this->never())->method('getOrder');

        $closure = function ($paymentMock) {
            return $paymentMock;
        };

        $this->assertEquals(
            $this->paymentMock,
            $this->removeCaptureCommentsPlugin->aroundCapture(
                $this->subjectMock,
                $closure,
                $this->paymentMock,
                $invoice
            )
        );
    }
}
