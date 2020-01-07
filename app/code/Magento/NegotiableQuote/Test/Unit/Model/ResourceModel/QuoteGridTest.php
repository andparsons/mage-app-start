<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\ResourceModel;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Test for Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteGridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Totals|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totals;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resources;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\Admin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resources = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteTotalsFactory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\TotalsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->totals = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\Totals::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCatalogTotalPrice', 'getSubtotal'])
            ->getMock();
        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getId'])
            ->getMockForAbstractClass();
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restriction = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\Admin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resource = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::class,
            [
                'resources' => $this->resources,
                'logger' => $this->logger,
                'companyManagement' => $this->companyManagement,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                'restriction' => $this->restriction,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'appState' => $this->appState
            ]
        );
    }

    /**
     * Test for method refresh.
     *
     * @param string $areaCode
     * @param int $subtotalCalls
     * @param int $quoteStatus
     * @dataProvider dataProviderTestRefresh
     * @return void
     */
    public function testRefresh($areaCode, $subtotalCalls, $quoteStatus)
    {
        $quoteId = 1;
        $customerId = 2;
        $companyId = 33;
        $companyName = 'Test Company';
        $baseCurrencyCode = 'USD';
        $quoteCurrencyCode = 'EUR';
        $baseToQuoteRate = 0.75;
        $totalPrice = 100;
        $salesRepName = 'Customer Name';
        $this->prepareResourcesMock();
        $this->customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $this->company->expects($this->exactly(2))->method('getSalesRepresentativeId')->willReturn($customerId);
        $this->company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->company->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->willReturn($this->company);
        $this->companyManagement->expects($this->exactly(2))
            ->method('getSalesRepresentative')->willReturn($salesRepName);
        $this->quote->expects($this->atLeastOnce())->method('getCustomer')->will($this->returnValue($this->customer));
        $this->totals->expects($this->atLeastOnce())->method('getCatalogTotalPrice')->willReturn($totalPrice);
        $this->totals->expects($this->exactly($subtotalCalls))->method('getSubtotal')->willReturn($totalPrice);
        $this->restriction->expects($this->once())->method('isLockMessageDisplayed')->willReturn(true);
        $this->appState->expects($this->once())->method('getAreaCode')->willReturn($areaCode);
        $this->quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getSnapshotQuote')->with($quoteId)->willReturn($this->quote);
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData'])
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getStatus')->willReturn($quoteStatus);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->quoteTotalsFactory->expects($this->atLeastOnce())
            ->method('create')->with(['quote' => $this->quote])->willReturn($this->totals);
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote->expects($this->once())->method('getCurrency')->willReturn($currency);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('hasData')
            ->withConsecutive([NegotiableQuoteInterface::QUOTE_STATUS], [NegotiableQuoteInterface::QUOTE_NAME])
            ->willReturn(true);
        $negotiableQuote->expects($this->once())->method('getQuoteName')->willReturn('Quote Name');
        $currency->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $currency->expects($this->once())->method('getQuoteCurrencyCode')->willReturn($quoteCurrencyCode);
        $currency->expects($this->once())->method('getBaseToQuoteRate')->willReturn($baseToQuoteRate);
        $result = $this->resource->refresh($this->quote);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Test refresh with exception.
     *
     * @return void
     */
    public function testRefreshWithException()
    {
        $baseCurrencyCode = 'USD';
        $quoteCurrencyCode = 'EUR';
        $baseToQuoteRate = 0.75;
        $exception = new \Exception();
        $this->logger->expects($this->once())->method('critical');
        $this->customer->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(14));
        $this->company->expects($this->exactly(2))->method('getSalesRepresentativeId')->will($this->returnValue(14));
        $this->companyManagement->expects($this->once())
            ->method('getByCustomerId')->will($this->returnValue($this->company));
        $this->quote->expects($this->atLeastOnce())->method('getCustomer')->will($this->returnValue($this->customer));
        $this->totals->expects($this->atLeastOnce())->method('getCatalogTotalPrice')->willReturn(100);

        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData'])
            ->getMockForAbstractClass();
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getStatus')->willReturn(NegotiableQuoteInterface::STATUS_ORDERED);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->quoteTotalsFactory->expects($this->once())->method('create')->willReturn($this->totals);
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quote->expects($this->once())->method('getCurrency')->willReturn($currency);
        $currency->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $currency->expects($this->once())->method('getQuoteCurrencyCode')->willReturn($quoteCurrencyCode);
        $currency->expects($this->once())->method('getBaseToQuoteRate')->willReturn($baseToQuoteRate);
        $this->resources->expects($this->once())->method('getConnection')->willThrowException($exception);
        $result = $this->resource->refresh($this->quote);

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Test refresh with exception where collecting Company Fields.
     *
     * @return void
     */
    public function testRefreshWithExceptionWhereCollectingCompanyFields()
    {
        $customerId = 14;
        $baseCurrencyCode = 'USD';
        $quoteCurrencyCode = 'EUR';
        $baseToQuoteRate = 0.75;
        $exception = new \Exception();
        $this->prepareResourcesMock();
        $this->customer->expects($this->exactly(3))->method('getId')->willReturn($customerId);
        $this->quote->expects($this->exactly(4))->method('getCustomer')->willReturn($this->customer);
        $this->restriction->expects($this->never())->method('isLockMessageDisplayed')->willReturn(false);
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData'])
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')
            ->willReturn($this->quote);

        $this->quoteTotalsFactory->expects($this->once())->method('create')->willReturn($this->totals);
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote->expects($this->once())->method('getCurrency')->willReturn($currency);
        $currency->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $currency->expects($this->once())->method('getQuoteCurrencyCode')->willReturn($quoteCurrencyCode);
        $currency->expects($this->once())->method('getBaseToQuoteRate')->willReturn($baseToQuoteRate);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->willThrowException($exception);
        $this->assertEquals($this->resource, $this->resource->refresh($this->quote));
    }

    /**
     * Test for method refreshValue.
     *
     * @return void
     */
    public function testRefreshValue()
    {
        $exception = new \Exception();
        $this->resources->expects($this->once())->method('getConnection')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical');
        $result = $this->resource->refreshValue(
            \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::QUOTE_ID,
            1,
            \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::COMPANY_NAME,
            2
        );

        $this->assertEquals($this->resource, $result);
    }

    /**
     * Test refreshValue with exception.
     *
     * @return void
     */
    public function testRefreshValueWithException()
    {
        $this->prepareResourcesMock();
        $this->connection->expects($this->once())->method('update');
        $result = $this->resource->refreshValue(
            \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::QUOTE_ID,
            1,
            \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::COMPANY_NAME,
            2
        );
        $this->assertEquals($this->resource, $result);
    }

    /**
     * Test for method refreshValue without update.
     *
     * @return void
     */
    public function testRefreshValueWithoutUpdate()
    {
        $this->connection->expects($this->never())->method('update');
        $result = $this->resource->refreshValue(
            'test',
            1,
            \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::COMPANY_NAME,
            2
        );
        $this->assertEquals($this->resource, $result);
    }

    /**
     * DataProvider for refresh method.
     *
     * @return array
     */
    public function dataProviderTestRefresh()
    {
        return [
            [\Magento\Framework\App\Area::AREA_ADMINHTML, 2, NegotiableQuoteInterface::STATUS_ORDERED],
            ['', 0, NegotiableQuoteInterface::STATUS_CREATED]
        ];
    }

    /**
     * Prepare resource mock.
     *
     * @return void
     */
    private function prepareResourcesMock()
    {
        $this->resources->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->resources->expects($this->once())->method('getTableName')->will($this->returnArgument(1));
    }
}
