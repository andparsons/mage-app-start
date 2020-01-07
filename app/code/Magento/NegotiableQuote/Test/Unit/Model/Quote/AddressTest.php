<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Quote;

use \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;

/**
 * Unit test for Address.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsCollectorMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restrictionMock;

    /**
     * @var NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemManagementMock;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepositoryMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHistoryMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applierMock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagementMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address
     */
    private $address;

    /**
     * Set up.
     *
     * @return @void
     */
    protected function setUp()
    {
        $this->quoteRepositoryMock = $this
            ->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'get',
                'save'
            ])
            ->getMockForAbstractClass();
        $this->totalsCollectorMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote\TotalsCollector::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'collectQuoteTotals',
                'collectAddressTotals'
            ])
            ->getMock();
        $this->restrictionMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['canSubmit'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteItemManagementMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'recalculateOriginalPriceTax'
            ])
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepositoryMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMockForAbstractClass();
        $this->quoteHistoryMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\History::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'collectTaxDataFromQuote',
                'collectOldDataFromQuote',
                'checkPricesAndDiscounts',
                'checkTaxes'
            ])
            ->getMock();
        $taxDataMock = $this
            ->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteHistoryMock->expects($this->any())
            ->method('collectTaxDataFromQuote')
            ->willReturn($taxDataMock);
        $this->quoteHistoryMock->expects($this->any())
            ->method('collectOldDataFromQuote')
            ->willReturn($taxDataMock);
        $this->applierMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setIsTaxChanged',
                'setIsAddressChanged'
            ])
            ->getMock();
        $this->addressRepositoryMock = $this
            ->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $addressMock = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->addressRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturn($addressMock);
        $this->negotiableQuoteManagementMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getNegotiableQuote',
                'updateProcessingByCustomerQuoteStatus'
            ])
            ->getMockForAbstractClass();
    }

    /**
     * Create testing object instance.
     *
     * @return void
     */
    private function createInstance()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->address = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Quote\Address::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'totalsCollector' => $this->totalsCollectorMock,
                'restriction' => $this->restrictionMock,
                'quoteItemManagement' => $this->negotiableQuoteItemManagementMock,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepositoryMock,
                'quoteHistory' => $this->quoteHistoryMock,
                'messageApplier' => $this->applierMock,
                'addressRepository' => $this->addressRepositoryMock,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagementMock
            ]
        );
    }

    /**
     * Test for updateQuoteShippingAddress() method when submitting is forbidden.
     *
     * @return void
     */
    public function testUpdateQuoteShippingAddressWhenSubmitForbidden()
    {
        $customerAddressMock = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->restrictionMock->expects($this->once())
            ->method('canSubmit')
            ->willReturn(false);

        $this->createInstance();
        $this->assertEquals(false, $this->address->updateQuoteShippingAddress(1, $customerAddressMock));
    }

    /**
     * Test for updateQuoteShippingAddress() method.
     *
     * @param bool $isTaxChanged
     * @dataProvider dataProviderUpdateQuoteShippingAddress
     * @return void
     */
    public function testUpdateQuoteShippingAddress($isTaxChanged)
    {
        $customerAddressMock = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $quoteMock = $this->buildNegotiableQuoteMock();
        $quoteMock->expects($this->any())
            ->method('isVirtual')
            ->willReturn(true);
        $this->negotiableQuoteManagementMock->expects($this->once())
            ->method('getNegotiableQuote')
            ->willReturn($quoteMock);
        $this->restrictionMock->expects($this->once())
            ->method('canSubmit')
            ->willReturn(true);
        $resultTaxDataMock = $this
            ->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsTaxChanged',
                'getIsShippingTaxChanged'
            ])
            ->getMock();
        $resultTaxDataMock->expects($this->once())
            ->method('getIsTaxChanged')
            ->willReturn($isTaxChanged);
        $resultTaxDataMock->expects($this->any())
            ->method('getIsShippingTaxChanged')
            ->willReturn($isTaxChanged);
        $this->quoteHistoryMock->expects($this->any())
            ->method('checkTaxes')
            ->willReturn($resultTaxDataMock);

        $this->createInstance();
        $this->assertEquals(true, $this->address->updateQuoteShippingAddress(1, $customerAddressMock));
    }

    /**
     * Test for updateQuoteShippingAddress() method with Exception.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to update shipping address
     * @return void
     */
    public function testUpdateQuoteShippingAddressException()
    {
        $customerAddressMock = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $quoteMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteManagementMock->expects($this->once())
            ->method('getNegotiableQuote')
            ->willReturn($quoteMock);
        $this->restrictionMock->expects($this->once())
            ->method('canSubmit')
            ->willReturn(true);
        $this->quoteHistoryMock->expects($this->once())
            ->method('collectTaxDataFromQuote')
            ->will(
                $this->throwException(new \Exception())
            );

        $this->createInstance();
        $this->address->updateQuoteShippingAddress(1, $customerAddressMock);
    }

    /**
     * Build NegotiableQuote mock with its dependencies.
     *
     * @param bool $isAddressDraft [optional]
     * @param string $snapshot [optional]
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildNegotiableQuoteMock($isAddressDraft = false, $snapshot = '[]')
    {
        $negotiableQuoteMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsAddressDraft',
                'getStatus',
                'setIsAddressDraft',
                'getNegotiatedPriceValue',
                'getSnapshot'
            ])
            ->getMockForAbstractClass();
        $negotiableQuoteMock->expects($this->any())
            ->method('getNegotiatedPriceValue')
            ->willReturn(null);
        $negotiableQuoteMock->expects($this->any())->method('getSnapshot')
            ->willReturn($snapshot);
        $negotiableQuoteMock->expects($this->any())->method('getIsAddressDraft')->willReturn($isAddressDraft);
        $quoteMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingAddress',
                'getBillingAddress',
                'isVirtual',
                'getExtensionAttributes',
                'removeAddress'
            ])
            ->getMock();
        $cartExtensionAttributes = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setShippingAssignments'])
            ->getMockForAbstractClass();
        $quoteMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($cartExtensionAttributes);
        $cartExtensionAttributes->expects($this->any())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuoteMock);

        $negotiableQuoteMock->expects($this->any())
            ->method('getIsAddressDraft')
            ->willReturn(true);
        $negotiableQuoteMock->expects($this->any())
            ->method('getStatus')
            ->willReturn('dummy_status');
        $shippingAddressMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'importCustomerAddressData',
                'setCollectShippingRates',
                'save',
                'delete'
            ])
            ->getMock();
        $shippingAddressMock->expects($this->any())
            ->method('importCustomerAddressData')
            ->willReturnSelf();
        $quoteMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($shippingAddressMock);
        $quoteMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($shippingAddressMock);

        return $quoteMock;
    }

    /**
     * Test for updateQuoteShippingAddressDraft() method.
     *
     * @param bool $isAddressDraft
     * @param string $snapshot
     * @return void
     * @dataProvider dataProviderUpdateQuoteShippingAddressDraft
     */
    public function testUpdateQuoteShippingAddressDraft($isAddressDraft, $snapshot)
    {
        $quoteMock = $this->buildNegotiableQuoteMock($isAddressDraft, $snapshot);
        $this->negotiableQuoteManagementMock->expects($this->any())
            ->method('getNegotiableQuote')
            ->willReturn($quoteMock);
        $this->restrictionMock->expects($this->any())->method('canProceedToCheckout')->willReturn(true);

        $this->createInstance();
        $this->address->updateQuoteShippingAddressDraft(1);
    }

    /**
     * Test for updateAddress() method.
     *
     * @param bool $isTaxChanged
     * @dataProvider dataProviderUpdateAddress
     * @return void
     */
    public function testUpdateAddress($isTaxChanged)
    {
        $quoteMock = $this->buildNegotiableQuoteMock();
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($quoteMock);
        $taxDataMock = $this
            ->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteHistoryMock->expects($this->any())
            ->method('collectTaxDataFromQuote')
            ->willReturn($taxDataMock);
        $this->quoteHistoryMock->expects($this->any())
            ->method('collectOldDataFromQuote')
            ->willReturn($taxDataMock);
        $resultTaxDataMock = $this
            ->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsTaxChanged',
                'getIsShippingTaxChanged'
            ])
            ->getMock();
        $resultTaxDataMock->expects($this->once())
            ->method('getIsTaxChanged')
            ->willReturn($isTaxChanged);
        $resultTaxDataMock->expects($this->any())
            ->method('getIsShippingTaxChanged')
            ->willReturn($isTaxChanged);
        $this->quoteHistoryMock->expects($this->any())
            ->method('checkTaxes')
            ->willReturn($resultTaxDataMock);

        $this->createInstance();
        $this->address->updateAddress(1, 1);
    }

    /**
     * testUpdateQuoteShippingAddress data Provider.
     *
     * @return array
     */
    public function dataProviderUpdateQuoteShippingAddress()
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * testUpdateAddress data Provider.
     *
     * @return array
     */
    public function dataProviderUpdateAddress()
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * DataProvider updateQuoteShippingAddressDraft.
     *
     * @return array
     */
    public function dataProviderUpdateQuoteShippingAddressDraft()
    {
        return [
            [false, '[]'],
            [true, '{"shipping_address":{"address_id":"shipping"},"billing_address":{"address_id":"billing"}}'],
            [true, '{"shipping_address":{"address_id":"shipping"},"billing_address":[]}']
        ];
    }
}
