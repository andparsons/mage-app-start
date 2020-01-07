<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Company\Model;

/**
 * Unit test for DataProvider.
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Plugin\Company\Model\DataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyFormatter;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditDataProvider = $this->getMockBuilder(\Magento\CompanyCredit\Api\CreditDataProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->currencyFormatter = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Company\Model\DataProvider::class,
            [
                'storeManager' => $this->storeManager,
                'creditDataProvider' => $this->creditDataProvider,
                'currencyFormatter' => $this->currencyFormatter
            ]
        );
    }

    /**
     * Test method for afterGetCompanyResultData.
     *
     * @return void
     */
    public function testAfterGetCompanyResultData()
    {
        $creditLimitValue = 100;
        $creditLimitFormattedValue = '100.00';
        $companyDataProvider = $this->getMockBuilder(\Magento\Company\Model\Company\DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditData = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditData->expects($this->once())->method('getExceedLimit')->willReturn(1);
        $creditData->expects($this->atLeastOnce())->method('getCurrencyCode')->willReturn('USD');
        $creditData->expects($this->once())->method('getCreditLimit')->willReturn($creditLimitValue);
        $this->currencyFormatter->expects($this->atLeastOnce())->method('formatTxt')
            ->willReturn($creditLimitFormattedValue);
        $this->creditDataProvider->expects($this->once())->method('get')->with(1)->willReturn($creditData);
        $result = ['id' => 1];
        $expected = [
            'id' => 1,
            'company_credit' => [
                'exceed_limit' => 1,
                'currency_code' => 'USD',
                'credit_limit' => $creditLimitFormattedValue
            ]
        ];

        $this->assertEquals($expected, $this->dataProvider->afterGetCompanyResultData($companyDataProvider, $result));
    }

    /**
     * Test method for afterGetCompanyResultData.
     *
     * @return void
     */
    public function testAfterGetCompanyResultDataWithoutCurrency()
    {
        $creditLimitValue = 100;
        $creditLimitFormattedValue = '100.00';
        $companyDataProvider = $this->getMockBuilder(\Magento\Company\Model\Company\DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditData = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditData->expects($this->once())->method('getExceedLimit')->willReturn(1);
        $creditData->expects($this->atLeastOnce())->method('getCurrencyCode')->willReturn(null);
        $creditData->expects($this->once())->method('getCreditLimit')->willReturn($creditLimitValue);
        $this->currencyFormatter->expects($this->atLeastOnce())->method('formatTxt')
            ->willReturn($creditLimitFormattedValue);
        $this->creditDataProvider->expects($this->once())->method('get')->with(1)->willReturn($creditData);
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currency->expects($this->once())->method('getCurrencyCode')->willReturn('USD');
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $result = ['id' => 1];
        $expected = [
            'id' => 1,
            'company_credit' => [
                'exceed_limit' => 1,
                'currency_code' => 'USD',
                'credit_limit' => $creditLimitFormattedValue
            ]
        ];

        $this->assertEquals($expected, $this->dataProvider->afterGetCompanyResultData($companyDataProvider, $result));
    }

    /**
     * Test method for afterGetCompanyResultData.
     *
     * @return void
     */
    public function testAfterGetCompanyResultDataWithoutId()
    {
        $companyDataProvider = $this->getMockBuilder(\Magento\Company\Model\Company\DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currency->expects($this->once())->method('getCurrencyCode')->willReturn('USD');
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $result = [];
        $expected = ['company_credit' => ['currency_code' => 'USD']];

        $this->assertEquals($expected, $this->dataProvider->afterGetCompanyResultData($companyDataProvider, $result));
    }
}
