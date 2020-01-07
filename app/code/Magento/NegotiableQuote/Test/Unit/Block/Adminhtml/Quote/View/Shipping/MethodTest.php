<?php
namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View\Shipping;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Shipping\Method|
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $method;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionQuoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCreate;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxData;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsCollector;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuote;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderCreate = $this->createMock(\Magento\Sales\Model\AdminOrder\Create::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->taxData = $helper->getObject(\Magento\Tax\Helper\Data::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->priceCurrency = $this->getMockForAbstractClass(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class,
            [],
            '',
            false,
            false,
            false,
            ['format', 'getCurrencySymbol']
        );
        $this->restriction = $this->createMock(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        );
        $this->negotiableQuote = $this->createMock(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class
        );
        $this->negotiableQuote->expects($this->any())->method('getIsRegularQuote')->will($this->returnValue(true));

        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            false,
            false,
            ['getNegotiableQuote']
        );
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')
            ->will($this->returnValue($this->negotiableQuote));
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->any())->method('getDefaultShipping')
            ->will($this->returnValue(1));
        $address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $this->quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            [
                'getBaseCurrencyCode',
                'getExtensionAttributes',
                'getCustomer',
                'getId',
                'getAddressesCollection',
                'getShippingAddress',
                'getBillingAddress',
                'getCurrency',
                'getStore'
            ]
        );
        $this->quote->expects($this->any())->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));
        $this->quote->expects($this->any())->method('getCustomer')
            ->will($this->returnValue($customer));
        $this->quote->expects($this->any())->method('getId')
            ->will($this->returnValue(2));
        $this->quote->expects($this->any())->method('getAddressesCollection')
            ->will($this->returnValue([]));
        $this->quote->expects($this->any())->method('getShippingAddress')
            ->will($this->returnValue($address));
        $this->quote->expects($this->any())->method('getBillingAddress')
            ->will($this->returnValue($address));
        $baseCurrencyCode = 'USD';
        $this->quote->expects($this->any())->method('getBaseCurrencyCode')
            ->willReturn($baseCurrencyCode);
        $address->expects($this->any())->method('getQuote')->willReturn($this->quote);
        $this->sessionQuoteMock = $this->createMock(\Magento\Backend\Model\Session\Quote::class);
        $this->totalsCollector = $this->createMock(\Magento\Quote\Model\Quote\TotalsCollector::class);
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($this->quote));
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->urlBuilderMock = $this->createMock(\Magento\Backend\Model\UrlInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->method = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Shipping\Method::class,
            [
                'sessionQuote' => $this->sessionQuoteMock,
                'orderCreate' => $this->orderCreate,
                'priceCurrency' => $this->priceCurrency,
                '_taxData' => $this->taxData,
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $this->restriction,
                'data' => [],
                '_urlBuilder' => $this->urlBuilderMock,
                'totalsCollector' => $this->totalsCollector,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test getQuote().
     *
     * @return void
     */
    public function testGetQuote()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with('quote_id')->will($this->returnValue(2));
        $this->negotiableQuote->expects($this->atLeastOnce())->method('getStatus')
            ->willReturn(NegotiableQuote::STATUS_CREATED);
        $this->totalsCollector->expects($this->once())->method('collectAddressTotals');
        $this->assertInstanceOf(\Magento\Quote\Model\Quote::class, $this->method->getQuote());
    }

    /**
     * Test getQuote().
     *
     * @return void
     */
    public function testGetQuoteNull()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with('quote_id')->will($this->returnValue(2));
        $this->quoteRepository->expects($this->any())->method('get')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertEquals(null, $this->method->getQuote());
    }

    /**
     * Test getQuote method for quote with ordered status.
     *
     * @return void
     */
    public function testGetQuoteWithOrderedStatus()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with('quote_id')->will($this->returnValue(2));
        $this->negotiableQuote->expects($this->once())->method('getStatus')
            ->willReturn(NegotiableQuote::STATUS_ORDERED);
        $this->totalsCollector->expects($this->never())->method('collectAddressTotals');
        $this->assertInstanceOf(\Magento\Quote\Model\Quote::class, $this->method->getQuote());
    }

    /**
     * Test getShippingMethodUrl().
     *
     * @return void
     */
    public function testGetShippingMethodUrl()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with('quote_id')->will($this->returnValue(2));
        $this->negotiableQuote->expects($this->atLeastOnce())->method('getStatus')
            ->willReturn(NegotiableQuote::STATUS_CREATED);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/shippingMethod/')
            ->willReturn('some value');
        $this->assertEquals('some value', $this->method->getShippingMethodUrl());
    }

    /**
     * Test getShippingMethodUrl() with exception.
     *
     * @return void
     */
    public function testGetShippingMethodUrlWithException()
    {
        $this->quoteRepository->expects($this->any())->method('get')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertEquals('', $this->method->getShippingMethodUrl());
    }

    /**
     * Test canEdit().
     *
     * @return void
     */
    public function testCanEdit()
    {
        $this->restriction->expects($this->once())->method('canSubmit')->willReturn(true);
        $this->assertTrue($this->method->canEdit());
    }

    /**
     * Test getProposedShippingPrice().
     *
     * @return void
     */
    public function testGetProposedShippingPrice()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturnMap(
            [
                ['isAjax', null, false],
                ['quote_id', null, 2]
            ]
        );
        $price = 3.5;
        $this->negotiableQuote->expects($this->once())->method('getShippingPrice')->willReturn($price);
        $this->assertEquals($price, $this->method->getProposedShippingPrice());
    }

    /**
     * Test getProposedShippingPrice() with ajax param.
     *
     * @return void
     */
    public function testGetProposedShippingPriceWithAjax()
    {
        $price = 3.5;
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturnMap(
            [
                ['isAjax', null, true],
                ['custom_shipping_price', null, $price]
            ]
        );
        $this->negotiableQuote->expects($this->never())->method('getShippingPrice');
        $this->assertEquals($price, $this->method->getProposedShippingPrice());
    }

    /**
     * Test getCurrencySymbol().
     *
     * @return void
     */
    public function testGetCurrencySymbol()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with('quote_id')->will($this->returnValue(2));
        $baseCurrencyCode = 'USD';
        $currency = $this->getMockBuilder(\Magento\Quote\Model\Cart\Currency::class)
            ->setMethods(['getBaseCurrencyCode'])
            ->disableOriginalConstructor()->getMock();
        $currency->expects($this->exactly(1))->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);

        $this->quote->expects($this->exactly(1))->method('getCurrency')->willReturn($currency);

        $symbol = '$';
        $this->priceCurrency->expects($this->once())->method('getCurrencySymbol')->willReturn($symbol);

        $this->assertEquals($symbol, $this->method->getCurrencySymbol());
    }

    /**
     * Test getOriginalShippingPrice() method.
     *
     * @param float $originalOriginalShippingPrice
     * @param float $price
     * @param string $expected
     * @dataProvider getOriginalShippingPriceDataProvider
     * @return void
     */
    public function testGetOriginalShippingPrice($originalOriginalShippingPrice, $price, $expected)
    {
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->quote->expects($this->exactly(2))->method('getStore')->will($this->returnValue($store));
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with('quote_id')->will($this->returnValue(2));
        $rate = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Rate::class)
            ->setMethods(['getOriginalShippingPrice', 'getPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $rate->expects($this->any())->method('getOriginalShippingPrice')->willReturn($originalOriginalShippingPrice);
        $rate->expects($this->any())->method('getPrice')->willReturn($price);

        $this->priceCurrency->expects($this->once())->method('format')->willReturn($expected);

        $this->assertEquals($expected, $this->method->getOriginalShippingPrice($rate, true));
    }

    /**
     * Data provider for getOriginalShippingPrice() method.
     *
     * @return array
     */
    public function getOriginalShippingPriceDataProvider()
    {
        $price = 3.5;
        $expected = '$' . $price;

        return [
            [
                $price, null, $expected
            ],
            [
                null, $price, $expected
            ]
        ];
    }
}
