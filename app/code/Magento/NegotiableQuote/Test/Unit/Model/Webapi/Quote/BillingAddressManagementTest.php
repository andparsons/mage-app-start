<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\Quote;

/**
 * Class BillingAddressManagementTest.
 */
class BillingAddressManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\BillingAddressManagementInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var int
     */
    private $addressId = 1;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\Quote\BillingAddressManagement
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $billingAddressManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->originalInterface = $this->createMock(\Magento\Quote\Api\BillingAddressManagementInterface::class);
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
                'getId',
                'getIsVirtual'
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
        $quote
            ->expects($this->any())
            ->method('getIsVirtual')
            ->willReturn(true);
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
        $this->billingAddressManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\Quote\BillingAddressManagement::class,
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
     * Test assign.
     *
     * @return void
     */
    public function testAssign()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        /**
         * @var \Magento\Quote\Api\Data\AddressInterface $address
         */
        $address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->originalInterface->expects($this->any())->method('assign')->willReturn($this->addressId);

        $this->assertEquals($this->addressId, $this->billingAddressManagement->assign($this->cartId, $address, false));
    }

    /**
     * Test get.
     *
     * @return void
     */
    public function testGet()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        /**
         * @var \Magento\Quote\Api\Data\AddressInterface $address
         */
        $address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->originalInterface->expects($this->any())->method('get')->willReturn($address);

        $this->assertEquals($address, $this->billingAddressManagement->get($this->cartId));
    }
}
