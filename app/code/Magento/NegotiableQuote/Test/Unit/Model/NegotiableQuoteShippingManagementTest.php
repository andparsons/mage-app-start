<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;

/**
 * Test for negotiable quote shipping method set.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NegotiableQuoteShippingManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var ShippingAssignmentProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentProcessor;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHistory;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteShippingManagement
     */
    private $object;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->negotiableQuoteManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAssignmentProcessor = $this->getMockBuilder(
            \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(
            \Magento\Quote\Api\CartRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->restriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setQuote'])
            ->getMockForAbstractClass();
        $this->quoteHistory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\History::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restriction->expects($this->once())
            ->method('setQuote')
            ->willReturn(1);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\NegotiableQuoteShippingManagement::class,
            [
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'shippingAssignmentProcessor' => $this->shippingAssignmentProcessor,
                'validatorFactory' => $this->validatorFactory,
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $this->restriction,
                'quoteHistory' => $this->quoteHistory,
            ]
        );
    }

    /**
     * Test setShippingMethod for negotiable quote.
     *
     * @return void
     */
    public function testSetShippingMethod()
    {
        $quoteId = 1;
        $shippingCode = 'flatrate_flatrate';

        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStatus'])
            ->getMockForAbstractClass();
        $negotiableQuote->expects($this->once())
            ->method('setStatus')
            ->with(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN)
            ->willReturn(true);
        $shippingRate = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMock();
        $shippingRate->expects($this->once())->method('getCode')->willReturn($shippingCode);
        $address = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId', 'getShippingRateByCode', 'setCollectShippingRates', 'collectShippingRates'])
            ->getMockForAbstractClass();
        $address->expects($this->once())->method('getCountryId')->willReturn('SU');
        $address->expects($this->once())->method('getShippingRateByCode')->willReturn($shippingRate);
        $shipping = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAddress', 'getMethod', 'setMethod'])
            ->getMockForAbstractClass();
        $shipping->expects($this->atLeastOnce())->method('getAddress')->willReturn($address);
        $shippingAssignments = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShipping'])
            ->getMockForAbstractClass();
        $shippingAssignments->expects($this->atLeastOnce())->method('getShipping')->willReturn($shipping);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAssignments', 'getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getShippingAssignments')
            ->willReturn([$shippingAssignments]);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsVirtual', 'getExtensionAttributes'])
            ->getMock();
        $quote->expects($this->once())->method('getIsVirtual')->willReturn(false);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')
            ->with($quoteId)
            ->willReturn($quote);
        $this->prepareCorrectValidator($quote);

        $result = $this->object->setShippingMethod($quoteId, $shippingCode);
        $this->assertTrue($result);
    }

    /**
     * Test setShippingMethod for negotiable quote with quote validate exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Cannot obtain the requested data. You must fix the errors listed below first.
     * @expectedExceptionMessage Message 1.
     * @expectedExceptionMessage Message 2.
     */
    public function testSetShippingMethodWithQuoteValidateException()
    {
        $quoteId = 1;
        $shippingCode = 'flatrate_flatrate';

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsVirtual', 'getExtensionAttributes'])
            ->getMock();
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')
            ->with($quoteId)
            ->willReturn($quote);

        $validatorResult = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasMessages', 'getMessages'])
            ->getMock();
        $validatorResult->expects($this->once())
            ->method('hasMessages')
            ->willReturn(true);
        $validatorResult->expects($this->once())
            ->method('getMessages')
            ->willReturn([__('Message 1'), __('Message 2')]);
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $validator->expects($this->once())
            ->method('validate')
            ->with(['quote' => $quote])
            ->willReturn($validatorResult);
        $this->validatorFactory->expects($this->once())
            ->method('create')
            ->with(['action' => 'edit'])
            ->willReturn($validator);

        $result = $this->object->setShippingMethod($quoteId, $shippingCode);
        $this->assertTrue($result);
    }

    /**
     * Test setShippingMethod for negotiable quote with quote is not active exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping method cannot be set for a virtual quote.
     */
    public function testSetShippingMethodWithQuoteIsNotActiveException()
    {
        $quoteId = 1;
        $shippingCode = 'flatrate_flatrate';

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsVirtual', 'getExtensionAttributes'])
            ->getMock();
        $quote->expects($this->once())->method('getIsVirtual')->willReturn(true);
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')
            ->with($quoteId)
            ->willReturn($quote);
        $this->prepareCorrectValidator($quote);

        $result = $this->object->setShippingMethod($quoteId, $shippingCode);
        $this->assertTrue($result);
    }

    /**
     * Test setShippingMethod for negotiable quote with no shipping address exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot add the shipping method. You must add a shipping address into the quote first.
     */
    public function testSetShippingMethodWithNoShippingAddressException()
    {
        $quoteId = 1;
        $shippingCode = 'flatrate_flatrate';

        $shippingAssignments = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShipping'])
            ->getMockForAbstractClass();
        $shippingAssignments->expects($this->atLeastOnce())->method('getShipping')->willReturn(null);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAssignments', 'getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getShippingAssignments')
            ->willReturn([$shippingAssignments]);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsVirtual', 'getExtensionAttributes'])
            ->getMock();
        $quote->expects($this->once())->method('getIsVirtual')->willReturn(false);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')
            ->with($quoteId)
            ->willReturn($quote);
        $this->prepareCorrectValidator($quote);

        $result = $this->object->setShippingMethod($quoteId, $shippingCode);
        $this->assertTrue($result);
    }

    /**
     * Test setShippingMethod for negotiable quote with no shipping rate exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Requested shipping method is not found. Row ID: ShippingMethodID = flatrate_flatrate.
     */
    public function testSetShippingMethodWithNoShippingRateException()
    {
        $quoteId = 1;
        $shippingCode = 'flatrate_flatrate';
        $shippingRate = null;
        $address = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryId', 'getShippingRateByCode', 'setCollectShippingRates', 'collectShippingRates'])
            ->getMockForAbstractClass();
        $address->expects($this->once())->method('getCountryId')->willReturn('SU');
        $address->expects($this->once())->method('getShippingRateByCode')->willReturn($shippingRate);
        $shipping = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAddress', 'getMethod', 'setMethod'])
            ->getMockForAbstractClass();
        $shipping->expects($this->atLeastOnce())->method('getAddress')->willReturn($address);
        $shippingAssignments = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShipping'])
            ->getMockForAbstractClass();
        $shippingAssignments->expects($this->atLeastOnce())->method('getShipping')->willReturn($shipping);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAssignments', 'getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getShippingAssignments')
            ->willReturn([$shippingAssignments]);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsVirtual', 'getExtensionAttributes'])
            ->getMock();
        $quote->expects($this->once())->method('getIsVirtual')->willReturn(false);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')
            ->with($quoteId)
            ->willReturn($quote);
        $this->prepareCorrectValidator($quote);

        $result = $this->object->setShippingMethod($quoteId, $shippingCode);
        $this->assertTrue($result);
    }

    /**
     * Prepate quote validator object that will return no errors.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    private function prepareCorrectValidator(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $validatorResult = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasMessages', 'getMessages'])
            ->getMock();
        $validatorResult->expects($this->once())
            ->method('hasMessages')
            ->willReturn([]);
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $validator->expects($this->once())
            ->method('validate')
            ->with(['quote' => $quote])
            ->willReturn($validatorResult);
        $this->validatorFactory->expects($this->once())
            ->method('create')
            ->with(['action' => 'edit'])
            ->willReturn($validator);
    }
}
