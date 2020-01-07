<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\Checkout;

/**
 * Class PaymentInformationManagementTest
 */
class PaymentInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\Checkout\PaymentInformationManagement
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInformationManagement;

    /**
     * @var int
     */
    private $cartId = 1;

    /**
     * @var int
     */
    private $orderId = 1;

    /**
     * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethod;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $billingAddress;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->originalInterface =
            $this->createMock(\Magento\Checkout\Api\PaymentInformationManagementInterface::class);
        $this->validator = $this->createMock(\Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class);
        $this->paymentMethod = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $this->billingAddress = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentInformationManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\Checkout\PaymentInformationManagement::class,
            [
                'originalInterface' => $this->originalInterface,
                'validator' => $this->validator
            ]
        );
    }

    /**
     * Test savePaymentInformationAndPlaceOrder
     */
    public function testSavePaymentInformationAndPlaceOrder()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        $this->originalInterface->expects($this->any())->method('savePaymentInformationAndPlaceOrder')
            ->willReturn($this->orderId);

        $this->assertEquals(
            $this->orderId,
            $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
                $this->cartId,
                $this->paymentMethod,
                $this->billingAddress
            )
        );
    }

    /**
     * Test savePaymentInformation
     */
    public function testSavePaymentInformation()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        $this->originalInterface->expects($this->any())->method('savePaymentInformation')->willReturn(true);

        $this->assertEquals(
            true,
            $this->paymentInformationManagement->savePaymentInformation(
                $this->cartId,
                $this->paymentMethod,
                $this->billingAddress
            )
        );
    }

    /**
     * Test getPaymentInformation
     */
    public function testGetPaymentInformation()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        $paymentInformation = $this->createMock(\Magento\Checkout\Api\Data\PaymentDetailsInterface::class);
        $this->originalInterface->expects($this->any())->method('getPaymentInformation')
            ->willReturn($paymentInformation);

        $this->assertInstanceOf(
            \Magento\Checkout\Api\Data\PaymentDetailsInterface::class,
            $this->paymentInformationManagement->getPaymentInformation($this->cartId)
        );
    }
}
