<?php

namespace Magento\Payment\Test\Unit\Model\Checks;

use \Magento\Payment\Model\Checks\CanUseForCurrency;

class CanUseForCurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Expected currency code
     */
    const EXPECTED_CURRENCY_CODE = 'US';

    /**
     * @var CanUseForCurrency
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new CanUseForCurrency();
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $paymentMethod = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseForCurrency')->with(
            self::EXPECTED_CURRENCY_CODE
        )->will($this->returnValue($expectation));

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $store = $this->getMockBuilder(
            \Magento\Store\Model\Store::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $store->expects($this->once())->method('getBaseCurrencyCode')->will(
            $this->returnValue(self::EXPECTED_CURRENCY_CODE)
        );
        $quoteMock->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->assertEquals($expectation, $this->_model->isApplicable($paymentMethod, $quoteMock));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
