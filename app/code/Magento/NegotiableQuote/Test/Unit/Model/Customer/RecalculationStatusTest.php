<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Customer;

/**
 * Unit test for Magento\NegotiableQuote\Model\Plugin\Customer\Model\RecalculateStatus class.
 */
class RecalculationStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressCollectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Customer\RecalculationStatus
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->taxHelper = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressCollectionFactory = $this->getMockBuilder(
            \Magento\Customer\Model\ResourceModel\Address\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Customer\RecalculationStatus::class,
            [
                'taxHelper' => $this->taxHelper,
                'addressCollectionFactory' => $this->addressCollectionFactory,
            ]
        );
    }

    /**
     * Test isNeedRecalculate method.
     *
     * @return void
     */
    public function testIsNeedRecalculate()
    {
        $addressId = 5;
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $oldAddress = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $addressCollection = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Address\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxHelper->expects($this->once())->method('getTaxBasedOn')->willReturn('origin');
        $this->addressCollectionFactory->expects($this->once())->method('create')->willReturn($addressCollection);
        $address->expects($this->atLeastOnce())->method('getId')->willReturn($addressId);
        $addressCollection->expects($this->once())
            ->method('addFilter')
            ->with(\Magento\Customer\Api\Data\AddressInterface::ID, $addressId)
            ->willReturnSelf();
        $addressCollection->expects($this->once())
            ->method('getItemById')
            ->with($addressId)
            ->willReturn($oldAddress);
        $oldAddress->expects($this->once())->method('getRegionId')->willReturn(2);
        $address->expects($this->once())->method('getRegionId')->willReturn(2);
        $oldAddress->expects($this->once())->method('getCountryId')->willReturn(2);
        $address->expects($this->once())->method('getCountryId')->willReturn(2);
        $oldAddress->expects($this->once())->method('getPostcode')->willReturn(220020);
        $address->expects($this->once())->method('getPostcode')->willReturn(223322);

        $this->assertTrue($this->model->isNeedRecalculate($address));
    }

    /**
     * Test isNeedRecalculate method.
     *
     * @return void
     */
    public function testIsNeedRecalculateEqualAddress()
    {
        $addressId = 5;
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $oldAddress = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $addressCollection = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Address\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxHelper->expects($this->once())->method('getTaxBasedOn')->willReturn('origin');
        $this->addressCollectionFactory->expects($this->once())->method('create')->willReturn($addressCollection);
        $address->expects($this->atLeastOnce())->method('getId')->willReturn($addressId);
        $addressCollection->expects($this->once())
            ->method('addFilter')
            ->with(\Magento\Customer\Api\Data\AddressInterface::ID, $addressId)
            ->willReturnSelf();
        $addressCollection->expects($this->once())
            ->method('getItemById')
            ->with($addressId)
            ->willReturn($oldAddress);
        $oldAddress->expects($this->once())->method('getRegionId')->willReturn(2);
        $address->expects($this->once())->method('getRegionId')->willReturn(2);
        $oldAddress->expects($this->once())->method('getCountryId')->willReturn(2);
        $address->expects($this->once())->method('getCountryId')->willReturn(2);
        $oldAddress->expects($this->once())->method('getPostcode')->willReturn(220020);
        $address->expects($this->once())->method('getPostcode')->willReturn(220020);

        $this->assertFalse($this->model->isNeedRecalculate($address));
    }
}
