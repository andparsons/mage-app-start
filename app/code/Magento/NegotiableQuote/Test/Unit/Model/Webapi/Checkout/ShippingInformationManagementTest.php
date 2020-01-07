<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\Checkout;

/**
 * Class ShippingInformationManagementTest
 */
class ShippingInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxHelper;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemManagement;

    /**
     * @var int
     */
    private $cartId = 1;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\Checkout\ShippingInformationManagement
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingInformationManagement;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->originalInterface =
            $this->createMock(\Magento\Checkout\Api\ShippingInformationManagementInterface::class);
        $this->validator = $this->createMock(\Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class);
        $this->quoteRepository = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['get']
        );
        $quote = $this->createMock(
            \Magento\Quote\Model\Quote::class,
            [
                'getExtensionAttributes',
                'getId'
            ]
        );
        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quoteNegotiation->expects($this->any())->method('getIsRegularQuote')->will($this->returnValue(true));
        $quoteNegotiation
            ->expects($this->any())
            ->method('setIsAddressDraft')
            ->with(true)
            ->will($this->returnValue(true));
        $quoteNegotiation->expects($this->any())->method('getNegotiatedPriceValue')->will($this->returnValue(null));
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $extensionAttributes
            ->expects($this->any())
            ->method('getNegotiableQuote')
            ->will($this->returnValue($quoteNegotiation));
        $quote
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));
        $quote
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));
        $this->taxHelper = $this->createMock(\Magento\Tax\Helper\Data::class);
        $this->taxHelper->expects($this->any())->method('getTaxBasedOn')->will($this->returnValue('shipping'));
        $this->quoteItemManagement = $this->getMockForAbstractClass(
            \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class
        );
        $this->quoteItemManagement->expects($this->any())
            ->method('recalculateOriginalPriceTax')
            ->with(1, true, true)
            ->willReturn(true);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shippingInformationManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\Checkout\ShippingInformationManagement::class,
            [
                'originalInterface' => $this->originalInterface,
                'validator' => $this->validator,
                'quoteRepository' => $this->quoteRepository,
                'quoteItemManagement' => $this->quoteItemManagement,
                'taxHelper' => $this->taxHelper
            ]
        );
    }

    /**
     * Test saveAddressInformation
     */
    public function testSaveAddressInformation()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        /**
         * @var \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
         */
        $addressInformation = $this->createMock(\Magento\Checkout\Api\Data\ShippingInformationInterface::class);
        $paymentDetails = $this->createMock(\Magento\Checkout\Api\Data\PaymentDetailsInterface::class);
        $this->originalInterface->expects($this->any())->method('saveAddressInformation')
            ->willReturn($paymentDetails);

        $this->assertInstanceOf(
            \Magento\Checkout\Api\Data\PaymentDetailsInterface::class,
            $this->shippingInformationManagement->saveAddressInformation($this->cartId, $addressInformation)
        );
    }
}
