<?php

namespace Magento\CompanyCredit\Test\Unit\Model\Config\Source\Locale;

/**
 * Class CurrencyTest.
 */
class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCurrency;

    /**
     * @var \Magento\Framework\Locale\Bundle\CurrencyBundle|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyBundle;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

    /**
     * @var \Magento\CompanyCredit\Model\Config\Source\Locale\Currency
     */
    private $currency;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->websiteCurrency = $this->createMock(
            \Magento\CompanyCredit\Model\WebsiteCurrency::class
        );
        $this->currencyBundle = $this->createMock(
            \Magento\Framework\Locale\Bundle\CurrencyBundle::class
        );
        $this->localeResolver = $this->createMock(
            \Magento\Framework\Locale\ResolverInterface::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->currency = $objectManager->getObject(
            \Magento\CompanyCredit\Model\Config\Source\Locale\Currency::class,
            [
                'websiteCurrency' => $this->websiteCurrency,
                'currencyBundle' => $this->currencyBundle,
                'localeResolver' => $this->localeResolver,
            ]
        );
    }

    /**
     * Test for toOptionArray method.
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $locale = 'en_US';
        $this->localeResolver->expects($this->once())->method('getLocale')->willReturn($locale);
        $this->currencyBundle->expects($this->once())->method('get')->with($locale)
            ->willReturn([
                'Currencies' => [
                    'EUR' => ['€', 'Euro'],
                    'USD' => ['$', 'US Dollar'],
                ],
            ]);
        $this->websiteCurrency->expects($this->once())
            ->method('getAllowedCreditCurrencies')->willReturn(['USD' => 'USD']);
        $this->assertEquals([['label' => 'US Dollar', 'value' => 'USD']], $this->currency->toOptionArray());
    }
}
