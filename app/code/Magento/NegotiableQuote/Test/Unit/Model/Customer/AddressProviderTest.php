<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AddressProviderTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Customer\AddressProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressProvider;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressConfig;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensibleDataObjectConverter;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->addressRepository = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->setMethods([
                'getList',
                'getById'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->addressConfig = $this->getMockBuilder(\Magento\Customer\Model\Address\Config::class)
            ->setMethods(['getFormatByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->setMethods([
                'setField',
                'setConditionType',
                'setValue',
                'create'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods([
                'addFilters',
                'create'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensibleDataObjectConverter = $this
            ->getMockBuilder(\Magento\Framework\Api\ExtensibleDataObjectConverter::class)
            ->setMethods(['toFlatArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->addressProvider = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Customer\AddressProvider::class,
            [
                'addressRepository' => $this->addressRepository,
                'addressConfig' => $this->addressConfig,
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverter,
                'customer' => $this->customer
            ]
        );
    }

    /**
     * Test getAllCustomerAddresses method.
     *
     * @return void
     */
    public function testGetAllCustomerAddresses()
    {
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $addresses = [$address];

        $customerId = 35;
        $this->customer->expects($this->once())->method('getId')->willReturn($customerId);

        $this->filterBuilder->expects($this->exactly(1))->method('setField')->willReturnSelf();
        $this->filterBuilder->expects($this->exactly(1))->method('setConditionType')->willReturnSelf();
        $this->filterBuilder->expects($this->exactly(1))->method('setValue')->willReturnSelf();
        $this->filterBuilder->expects($this->exactly(1))->method('create')->willReturnSelf();

        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();

        $this->searchCriteriaBuilder->expects($this->exactly(1))->method('addFilters')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->exactly(1))->method('create')->willReturn($searchCriteria);

        $searchList = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressSearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $searchList->expects($this->exactly(1))->method('getItems')->willReturn($addresses);

        $this->addressRepository->expects($this->exactly(1))->method('getList')->willReturn($searchList);

        $this->assertEquals($addresses, $this->addressProvider->getAllCustomerAddresses());
    }

    /**
     * Test getAllCustomerAddresses method with Exception.
     *
     * @return void
     */
    public function testGetAllCustomerAddressesWithException()
    {
        $addresses = [];

        $phrase = new \Magento\Framework\Phrase('message');
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->customer->expects($this->once())->method('getId')->willThrowException($exception);

        $this->assertEquals($addresses, $this->addressProvider->getAllCustomerAddresses());
    }

    /**
     * Test getRenderedAddress method.
     *
     * @return void
     */
    public function testGetRenderedAddress()
    {
        $resultAddress = 'City, California, 12323';

        $address = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $addressData = [
            \Magento\Quote\Api\Data\AddressInterface::KEY_POSTCODE => '25235'
        ];
        $this->extensibleDataObjectConverter->expects($this->exactly(1))->method('toFlatArray')
            ->with($address, [], \Magento\Quote\Api\Data\AddressInterface::class)
            ->willReturn($addressData);
        $this->setUpRendererMock($resultAddress);

        $this->assertEquals($resultAddress, $this->addressProvider->getRenderedAddress($address));
    }

    /**
     * Test getRenderedLineAddress method.
     *
     * @return void
     */
    public function testGetRenderedLineAddress()
    {
        $addressId = 64;
        $resultAddress = 'City, California, 12323';

        $street = ['City', 'California', '12323', 'City, California, 12323'];
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->setMethods(['getStreet'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $address->expects($this->exactly(1))->method('getStreet')->willReturn($street);

        $this->addressRepository->expects($this->exactly(1))->method('getById')->willReturn($address);

        $addressData = [1, 2, 3];
        $this->extensibleDataObjectConverter->expects($this->exactly(1))->method('toFlatArray')
            ->with($address, [], \Magento\Customer\Api\Data\AddressInterface::class)
            ->willReturn($addressData);
        $this->setUpRendererMock($resultAddress);

        $this->assertEquals($resultAddress, $this->addressProvider->getRenderedLineAddress($addressId));
    }

    /**
     * Set up Renderer Mock.
     *
     * @param string $resultAddress
     * @return void
     */
    private function setUpRendererMock($resultAddress)
    {
        $renderer = $this->getMockBuilder(\Magento\Customer\Block\Address\Renderer\RendererInterface::class)
            ->setMethods(['renderArray'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $renderer->expects($this->exactly(1))->method('renderArray')->willReturn($resultAddress);

        $dataObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getRenderer'])
            ->disableOriginalConstructor()->getMock();
        $dataObject->expects($this->exactly(1))->method('getRenderer')->willReturn($renderer);
        $this->addressConfig->expects($this->exactly(1))->method('getFormatByCode')->willReturn($dataObject);
    }

    /**
     * Test getRenderedLineAddress method with Exception.
     *
     * @return void
     */
    public function testGetRenderedLineAddressWithException()
    {
        $addressId = 64;
        $resultAddress = '';

        $phrase = new \Magento\Framework\Phrase('message');
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->addressRepository->expects($this->exactly(1))->method('getById')->willThrowException($exception);

        $this->assertEquals($resultAddress, $this->addressProvider->getRenderedLineAddress($addressId));
    }
}
