<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Class WebsiteCurrencyTest.
 */
class WebsiteCurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyFactory;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->storeManager = $this->createMock(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $website = $this->createMock(
            \Magento\Store\Model\Website::class
        );
        $website->method('getBaseCurrencyCode')->willReturn('USD');
        $this->storeManager->method('getWebsites')->willReturn([$website]);
        $this->currencyFactory = $this->createPartialMock(
            \Magento\Directory\Model\CurrencyFactory::class,
            ['create']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->websiteCurrency = $objectManager->getObject(
            \Magento\CompanyCredit\Model\WebsiteCurrency::class,
            [
                'storeManager' => $this->storeManager,
                'currencyFactory' => $this->currencyFactory,
            ]
        );
    }

    /**
     * Test isCreditCurrencyEnabled method.
     */
    public function testIsCreditCurrencyEnabled()
    {
        $this->assertTrue($this->websiteCurrency->isCreditCurrencyEnabled('USD'));
    }

    /**
     * Test getAllowedCreditCurrencies method.
     */
    public function testGetAllowedCreditCurrencies()
    {
        $this->assertNotEmpty($this->websiteCurrency->getAllowedCreditCurrencies());
        $this->assertEquals(1, count($this->websiteCurrency->getAllowedCreditCurrencies()));
    }

    /**
     * Test getCurrencyByCode method.
     */
    public function testGetCurrencyByCode()
    {
        $code = 'Euro';
        $currency = $this->createMock(
            \Magento\Directory\Model\Currency::class
        );
        $currency->method('load')->willReturn('codeInstance');
        $this->currencyFactory->method('create')->willReturn($currency);
        $this->assertNotEmpty($this->websiteCurrency->getCurrencyByCode($code));
        $this->assertEquals('codeInstance', $this->websiteCurrency->getCurrencyByCode($code));
    }

    /**
     * Test getCurrencyByCode method.
     */
    public function testGetCurrencyByCodeWithEmptyParameter()
    {
        $code = 'Euro';
        $store = $this->createMock(
            \Magento\Store\Model\Store::class
        );
        $store->method('getBaseCurrency')->willReturn($code);
        $this->storeManager->method('getStore')->willReturn($store);
        $this->assertEquals(
            $code,
            $this->websiteCurrency->getCurrencyByCode(false)
        );
    }
}
