<?php

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Setup\Lists;

class ListsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Lists
     */
    protected $lists;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Locale\ConfigInterface
     */
    protected $mockConfig;

    /**
     * @var array
     */
    protected $expectedTimezones = [
        'Australia/Darwin',
        'America/Los_Angeles',
        'Europe/Kiev',
        'Asia/Jerusalem',
    ];

    /**
     * @var array
     */
    protected $expectedCurrencies = [
        'USD',
        'EUR',
        'UAH',
        'GBP',
    ];

    /**
     * @var array
     */
    protected $expectedLocales = [
        'en_US',
        'en_GB',
        'uk_UA',
        'de_DE',
    ];

    protected function setUp()
    {
        $this->mockConfig = $this->getMockBuilder(\Magento\Framework\Locale\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig->expects($this->any())
            ->method('getAllowedLocales')
            ->willReturn($this->expectedLocales);
        $this->mockConfig->expects($this->any())
            ->method('getAllowedCurrencies')
            ->willReturn($this->expectedCurrencies);

        $this->lists = new Lists($this->mockConfig);
    }

    public function testGetTimezoneList()
    {
        $timezones = array_intersect($this->expectedTimezones, array_keys($this->lists->getTimezoneList()));
        $this->assertEquals($this->expectedTimezones, $timezones);
    }

    public function testGetLocaleList()
    {
        $locales = array_intersect($this->expectedLocales, array_keys($this->lists->getLocaleList()));
        $this->assertEquals($this->expectedLocales, $locales);
    }

    /**
     * Test Lists:getCurrencyList() considering allowed currencies config values.
     */
    public function testGetCurrencyList()
    {
        $currencies = array_intersect($this->expectedCurrencies, array_keys($this->lists->getCurrencyList()));
        $this->assertEquals($this->expectedCurrencies, $currencies);
    }
}
