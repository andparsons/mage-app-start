<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Unit test for Info.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Info
     */
    private $info;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\NegotiableQuote\Model\Expiration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expiration;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\Customer\AddressProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\Customer\AddressProviderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressProviderFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Company\DetailsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyDetailsProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\Company\DetailsProviderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyDetailsProviderFactory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->negotiableQuoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);

        $this->expiration = $this->createMock(\Magento\NegotiableQuote\Model\Expiration::class);

        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);

        $this->prepareQuoteMock();

        $localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $localeDate->expects($this->any())->method('formatDateTime')->will($this->returnArgument(1));

        $labelProvider = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\Status\BackendLabelProvider::class,
            []
        );

        $this->addressProvider = null;
        $this->addressProviderFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Customer\AddressProviderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->companyDetailsProvider = null;
        $this->companyDetailsProviderFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Company\DetailsProviderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->info = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\Info::class,
            [
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'addressProvider' => $this->addressProvider,
                'addressProviderFactory' => $this->addressProviderFactory,
                'companyDetailsProvider' => $this->companyDetailsProvider,
                'companyDetailsProviderFactory' => $this->companyDetailsProviderFactory,
                'labelProvider' => $labelProvider,
                'expiration' => $this->expiration,
                '_localeDate' => $localeDate,
                '_urlBuilder' => $this->urlBuilder,
                'data' => []
            ]
        );
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->info->setLayout($layout);
    }

    /**
     * Prepare Quote mock.
     *
     * @return void
     */
    private function prepareQuoteMock()
    {
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['getShippingAddress', 'getCustomer', 'getCurrency'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->negotiableQuoteHelper->expects($this->any())
            ->method('resolveCurrentQuote')
            ->willReturn($this->quote);
    }

    /**
     * Test getQuoteStatusLabel method.
     *
     * @dataProvider getQuoteStatusLabelDataProvider
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|null $negotiableQuote
     * @param bool $expectedResult
     * @return void
     */
    public function testGetQuoteStatusLabel($negotiableQuote, $expectedResult)
    {
        /** @var \Magento\Quote\Api\Data\CartExtensionInterface $extensionAttributes */
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();

        $this->quote
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));

        $extensionAttributes
            ->expects($this->any())
            ->method('getNegotiableQuote')
            ->will($this->returnValue($negotiableQuote));

        $this->assertEquals($expectedResult, $this->info->getQuoteStatusLabel());
    }

    /**
     * Data provider for testGetQuoteStatusLabel.
     *
     * @return array
     */
    public function getQuoteStatusLabelDataProvider()
    {
        $quoteArray = [];
        //null quote
        $quoteArray[] = [null, ''];

        //quote with status
        $quoteNegotiation = $this->createMock(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class
        );
        $quoteNegotiation->expects($this->any())
            ->method('getStatus')->will($this->returnValue(NegotiableQuoteInterface::STATUS_CREATED));
        $quoteArray[] = [$quoteNegotiation, 'New'];

        //quote without status
        $quoteNegotiation = $this->createMock(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class
        );
        $quoteArray[] = [$quoteNegotiation, ''];

        return $quoteArray;
    }

    /**
     * Test getAddressHtml method.
     *
     * @return void
     */
    public function testGetAddressHtml()
    {
        $addressHtml = 'Test Address';

        $shippingAddress = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->quote->expects($this->once())->method('getCustomer')->willReturn($customer);
        $this->quote->expects($this->once())->method('getShippingAddress')->will($this->returnValue($shippingAddress));
        $this->negotiableQuoteHelper->expects($this->exactly(2))
            ->method('resolveCurrentQuote')->willReturn($this->quote);

        $addressProvider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Customer\AddressProvider::class)
            ->disableOriginalConstructor()->getMock();
        $addressProvider->expects($this->once())->method('getRenderedAddress')
            ->with($shippingAddress)->willReturn($addressHtml);
        $this->addressProviderFactory->expects($this->once())->method('create')->willReturn($addressProvider);

        $this->assertEquals($addressHtml, $this->info->getAddressHtml());
    }

    /**
     * Test getQuoteOwnerFullName method.
     *
     * @return void
     */
    public function testGetQuoteOwnerFullName()
    {
        $ownerFullName = 'Test Owner Name';

        $this->negotiableQuoteHelper->expects($this->once())->method('resolveCurrentQuote')->willReturn($this->quote);

        $companyDetailsProvider = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Company\DetailsProvider::class)
            ->disableOriginalConstructor()->getMock();
        $companyDetailsProvider->expects($this->once())->method('getQuoteOwnerName')->willReturn($ownerFullName);
        $this->companyDetailsProviderFactory->expects($this->once())
            ->method('create')->willReturn($companyDetailsProvider);

        $this->assertEquals($ownerFullName, $this->info->getQuoteOwnerFullName());
    }

    /**
     * Test getSalesRep method.
     *
     * @return  void
     */
    public function testGetSalesRep()
    {
        $salesRepresentativeName = 'Test Name';

        $this->negotiableQuoteHelper->expects($this->once())->method('resolveCurrentQuote')->willReturn($this->quote);

        $companyDetailsProvider = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Company\DetailsProvider::class)
            ->disableOriginalConstructor()->getMock();
        $companyDetailsProvider->expects($this->once())->method('getSalesRepresentativeName')
            ->willReturn($salesRepresentativeName);

        $this->companyDetailsProviderFactory->expects($this->once())
            ->method('create')->willReturn($companyDetailsProvider);
        $this->assertEquals($salesRepresentativeName, $this->info->getSalesRep());
    }

    /**
     * Test getQuoteCreatedAt method.
     *
     * @param int $createdAt
     * @param bool $expectedResult
     * @dataProvider getQuoteCreatedAtDataProvider
     * @return void
     */
    public function testGetQuoteCreatedAt($createdAt, $expectedResult)
    {
        if (!empty($createdAt)) {
            $this->quote->expects($this->once())->method('getCreatedAt')->will($this->returnValue($createdAt));
        } else {
            $this->negotiableQuoteHelper
                ->expects($this->exactly(2))
                ->method('resolveCurrentQuote')
                ->willReturn(null);
        }

        $this->assertEquals($expectedResult, $this->info->getQuoteCreatedAt());
    }

    /**
     * Data provider for testGetQuoteCreatedAt.
     *
     * @return array
     */
    public function getQuoteCreatedAtDataProvider()
    {
        return [
            ['2015-12-22 17:55:51', \IntlDateFormatter::MEDIUM],
            [0, \IntlDateFormatter::MEDIUM],
        ];
    }

    /**
     * Test getQuoteName method.
     *
     * @dataProvider getQuoteNameDataProvider
     * @param string|bool $quoteName
     * @param bool $expectedResult
     * @return void
     */
    public function testGetQuoteName($quoteName, $expectedResult)
    {
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $this->quote
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));

        if ($quoteName) {
            $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class);
            $quoteNegotiation->expects($this->any())
                ->method('getQuoteName')->will($this->returnValue($quoteName));
            $this->quote
                ->getExtensionAttributes()
                ->expects($this->any())
                ->method('getNegotiableQuote')
                ->will($this->returnValue($quoteNegotiation));
        }

        $this->assertEquals($expectedResult, $this->info->getQuoteName());
    }

    /**
     * Data provider for testGetQuoteName.
     *
     * @return array
     */
    public function getQuoteNameDataProvider()
    {
        return [
            [false, ''],
            ['test', 'test']
        ];
    }

    /**
     * Test getExpirationPeriodTime.
     *
     * @return void
     */
    public function testGetExpirationPeriodTime()
    {
        $date = new \DateTime;
        $this->expiration->expects($this->once())->method('getExpirationPeriodTime')->willReturn($date);

        $this->assertEquals($date, $this->info->getExpirationPeriodTime());
    }

    /**
     * Test isQuoteExpirationDateDisplayed.
     *
     * @param int $timeStamp
     * @param bool $expectedResult
     * @dataProvider isQuoteExpirationDateDisplayedDataProvider
     * @return void
     */
    public function testIsQuoteExpirationDateDisplayed($timeStamp, $expectedResult)
    {
        $date = $this->createMock(
            \DateTime::class
        );
        $this->expiration->expects($this->exactly(2))->method('getExpirationPeriodTime')->willReturn($date);
        $date->expects($this->once())->method('getTimestamp')->willReturn($timeStamp);

        $this->assertEquals($expectedResult, $this->info->isQuoteExpirationDateDisplayed());
    }

    /**
     * Data provider for testIsQuoteExpirationDateDisplayed.
     *
     * @return array
     */
    public function isQuoteExpirationDateDisplayedDataProvider()
    {
        return [
            [1466063520, true],
            [null, false]
        ];
    }

    /**
     * Test getAllAddresses.
     *
     * @return void
     */
    public function testGetAllAddresses()
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->quote->expects($this->once())->method('getCustomer')->willReturn($customer);
        $this->negotiableQuoteHelper->expects($this->once())->method('resolveCurrentQuote')->willReturn($this->quote);

        $customerAddresses = [
            'Customer Address 1',
            'Customer Address 2'
        ];
        $addressProvider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Customer\AddressProvider::class)
            ->disableOriginalConstructor()->getMock();
        $addressProvider->expects($this->once())->method('getAllCustomerAddresses')->willReturn($customerAddresses);
        $this->addressProviderFactory->expects($this->once())->method('create')->willReturn($addressProvider);

        $this->assertEquals($customerAddresses, $this->info->getAllAddresses());
    }

    /**
     * Test isDefaultAddress method.
     *
     * @dataProvider isDefaultAddressDataProvider
     * @param int $customerId
     * @param int $addressId
     * @param array $call
     * @param bool $expectedResult
     * @return void
     */
    public function testIsDefaultAddress($customerId, $addressId, $expectedResult, array $call)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $customer->expects($this->once())->method('getDefaultShipping')->willReturn($customerId);

        $shippingAddress = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCustomerAddressId']
        );
        $shippingAddress->expects($this->exactly($call['getCustomerAddressId']))
            ->method('getCustomerAddressId')->willReturn($customerId);

        $this->quote->expects($this->once())->method('getCustomer')->willReturn($customer);
        $this->quote->expects($this->exactly($call['getShippingAddress']))
            ->method('getShippingAddress')->willReturn($shippingAddress);
        $this->negotiableQuoteHelper->expects($this->exactly($call['resolveCurrentQuote']))
            ->method('resolveCurrentQuote')->willReturn($this->quote);

        $this->assertEquals($expectedResult, $this->info->isDefaultAddress($addressId));
    }

    /**
     * DataProvider for testIsDefaultAddress method.
     *
     * @return array
     */
    public function isDefaultAddressDataProvider()
    {
        return [
            [
                1, 1, true,
                [
                    'getCustomerAddressId' => 0,
                    'getShippingAddress' => 0,
                    'resolveCurrentQuote' => 1,
                ]
            ],
            [
                1, 2, false,
                [
                    'getCustomerAddressId' => 1,
                    'getShippingAddress' => 2,
                    'resolveCurrentQuote' => 4,
                ]
            ]
        ];
    }

    /**
     * Test getLineAddressHtml method.
     *
     * @return  void
     */
    public function testGetLineAddressHtml()
    {
        $addressId = 46;
        $renderedLineAddress = 'Test Line Address';

        $addressProvider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Customer\AddressProvider::class)
            ->disableOriginalConstructor()->getMock();
        $addressProvider->expects($this->once())->method('getRenderedLineAddress')
            ->with($addressId)->willReturn($renderedLineAddress);
        $this->addressProviderFactory->expects($this->once())->method('create')->willReturn($addressProvider);

        $this->assertEquals($renderedLineAddress, $this->info->getLineAddressHtml($addressId));
    }

    /**
     * Test getQuoteShippingAddressId.
     *
     * @dataProvider getQuoteShippingAddressIdDataProvider
     *
     * @param int|null $id
     * @param bool $expectedResult
     * @return void
     */
    public function testGetQuoteShippingAddressId($id, $expectedResult)
    {
        $shippingAddress = $this->createPartialMock(
            \Magento\Framework\Api\ExtensibleDataInterface::class,
            ['getId']
        );
        $this->quote->expects($this->any())
            ->method('getShippingAddress')->willReturn($shippingAddress);
        $shippingAddress->expects($this->any())
            ->method('getId')->willReturn($id);
        $this->assertEquals($expectedResult, $this->info->getQuoteShippingAddressId());
    }

    /**
     * DataProvider for testGetQuoteShippingAddressId.
     *
     * @return array
     */
    public function getQuoteShippingAddressIdDataProvider()
    {
        return [
            [1, true],
            [null, false]
        ];
    }

    /**
     * Test getAddShippingAddressUrl method.
     *
     * @return void
     */
    public function testGetAddShippingAddressUrl()
    {
        $path = 'customer/address/new';
        $url = 'http://example.com/';
        $quoteId = 1;
        $this->quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->urlBuilder->expects($this->any())
            ->method('getUrl')
            ->with('customer/address/new', ['quoteId' => $quoteId])
            ->willReturn($url . $path . '/quoteId/' . $quoteId . '/');

        $this->assertEquals($url . $path . '/quoteId/' . $quoteId . '/', $this->info->getAddShippingAddressUrl());
    }

    /**
     * Test getUpdateShippingAddressUrl.
     *
     * @return void
     */
    public function testGetUpdateShippingAddressUrl()
    {
        $path = '*/*/updateAddress';
        $url = 'http://example.com/';
        $this->urlBuilder->expects($this->any())
            ->method('getUrl')->willReturn($url . $path);

        $this->assertEquals($url . $path, $this->info->getUpdateShippingAddressUrl());
    }

    /**
     * Prepare Currency mock.
     *
     * @param array $returned
     * @param array $calls
     * @return void
     */
    private function prepareCurrencyMock(array $returned, array $calls)
    {
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->setMethods(['getBaseCurrencyCode', 'getQuoteCurrencyCode', 'getBaseToQuoteRate'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $currency->expects($this->exactly($calls['currency_getBaseCurrencyCode']))->method('getBaseCurrencyCode')
            ->willReturn($returned['currency_getBaseCurrencyCode']);

        $currency->expects($this->exactly($calls['currency_getQuoteCurrencyCode']))->method('getQuoteCurrencyCode')
            ->willReturn($returned['currency_getQuoteCurrencyCode']);

        $currency->expects($this->exactly($calls['currency_getBaseToQuoteRate']))->method('getBaseToQuoteRate')
            ->willReturn($returned['currency_getBaseToQuoteRate']);

        $this->quote->expects($this->exactly(1))->method('getCurrency')->willReturn($currency);
    }

    /**
     * Test getCurrencyRateLabel() method.
     *
     * @param string $baseCurrencyCode
     * @param string $quoteCurrencyCode
     * @param string $expects
     * @param array $calls
     * @dataProvider getCurrencyRateLabelDataProvider
     * @return void
     */
    public function testGetCurrencyRateLabel($baseCurrencyCode, $quoteCurrencyCode, $expects, array $calls)
    {
        $returned = [
            'currency_getBaseCurrencyCode' => $baseCurrencyCode,
            'currency_getQuoteCurrencyCode' => $quoteCurrencyCode,
            'currency_getBaseToQuoteRate' => 1
        ];
        $this->prepareCurrencyMock($returned, $calls);

        $this->assertEquals($expects, $this->info->getCurrencyRateLabel());
    }

    /**
     * Data provider for getCurrencyRateLabel() method.
     *
     * @return array
     */
    public function getCurrencyRateLabelDataProvider()
    {
        $calls = ['currency_getBaseToQuoteRate' => 0];
        return [
            [
                'USD', 'EUR', 'USD / EUR',
                ['currency_getBaseCurrencyCode' => 2, 'currency_getQuoteCurrencyCode' => 2] + $calls
            ],
            [
                'EUR', 'EUR', '',
                ['currency_getBaseCurrencyCode' => 1, 'currency_getQuoteCurrencyCode' => 1] + $calls
            ]
        ];
    }

    /**
     * Test getCurrencyRate() method.
     *
     * @param float $baseCurrencyCode
     * @param float $quoteCurrencyCode
     * @param float $expects
     * @param array $calls
     * @dataProvider getCurrencyRateDataProvider
     * @return void
     */
    public function testGetCurrencyRate($baseCurrencyCode, $quoteCurrencyCode, $expects, array $calls)
    {
        $returned = [
            'currency_getBaseCurrencyCode' => $baseCurrencyCode,
            'currency_getQuoteCurrencyCode' => $quoteCurrencyCode,
            'currency_getBaseToQuoteRate' => $expects
        ];

        $this->prepareCurrencyMock($returned, $calls);

        $this->assertEquals($expects, $this->info->getCurrencyRate());
    }

    /**
     * Data provider for getCurrencyRate() method.
     *
     * @return array
     */
    public function getCurrencyRateDataProvider()
    {
        return [
            [
                'USD', 'EUR', 1.6,
                [
                    'currency_getBaseCurrencyCode' => 1,
                    'currency_getQuoteCurrencyCode' => 1,
                    'currency_getBaseToQuoteRate' => 2
                ]
            ],
            [
                'EUR', 'EUR', 1,
                [
                    'currency_getBaseCurrencyCode' => 1,
                    'currency_getQuoteCurrencyCode' => 1,
                    'currency_getBaseToQuoteRate' => 0
                ]
            ]
        ];
    }
}
