<?php

namespace Magento\NegotiableQuote\Test\Unit\Helper;

/**
 * Class ConfigTest
 * @package Magento\NegotiableQuote\Test\Unit\Helper
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Config
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\NegotiableQuote\Helper\Config::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->configMock = $context->getScopeConfig();
        /** @var \Magento\Framework\App\Helper\Context $context */
        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @covers \Magento\NegotiableQuote\Helper\Config::isQuoteAllowed
     * @param $isAllowExpected
     * @dataProvider isQuoteAllowDataProvider
     */
    public function testIsQuoteAllow($isAllowExpected)
    {
        $quoteMock =
            $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getSubtotalWithDiscount', 'getStoreId']);

        /** @var \Magento\NegotiableQuote\Helper\Config $helperMock */
        $helperMock = $this->createPartialMock(\Magento\NegotiableQuote\Helper\Config::class, ['isAllowedAmount'], []);

        $helperMock->expects($this->once())->method('isAllowedAmount')->will($this->returnValue($isAllowExpected));

        $isAllow = $helperMock->isQuoteAllowed($quoteMock);
        $this->assertEquals($isAllowExpected, $isAllow);
    }

    /**
     * @return array
     */
    public function isQuoteAllowDataProvider()
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @covers \Magento\NegotiableQuote\Helper\Config::isAllowedAmount
     * @dataProvider getIsAllowAmountProvider
     * @param $amount
     * @param $minimumAmount
     * @param $expectedIsAllow
     */
    public function testIsAllowAmount($amount, $minimumAmount, $expectedIsAllow)
    {
        $helperMock = $this->createPartialMock(
            \Magento\NegotiableQuote\Helper\Config::class,
            ['getMinimumAmount']
        );

        $helperMock->expects($this->once())->method('getMinimumAmount')->will($this->returnValue($minimumAmount));

        $isAllow = $helperMock->isAllowedAmount($amount);
        $this->assertEquals($expectedIsAllow, $isAllow);
    }

    /**
     * @return array
     */
    public function getIsAllowAmountProvider()
    {
        return [
            [10, 100, false],
            [100, 100, true],
            [110, 100, true]
        ];
    }

    /**
     * @covers \Magento\NegotiableQuote\Helper\Config::getMinimumAmount
     * @dataProvider getMinimumAmountProvider
     */
    public function testGetMinimumAmount($configValue, $expectedAmount)
    {
        $this->configMock->expects($this->any())->method('getValue')
            ->with('quote/general/minimum_amount')
            ->will($this->returnValue($configValue));

        $amount = $this->helper->getMinimumAmount();

        $this->assertEquals($amount, $expectedAmount);
    }

    /**
     * @return array
     */
    public function getMinimumAmountProvider()
    {
        return [
            ['100', 100],
            ['', 0],
            ['100.5464', 100.5464],
            ['not_number', 0]
        ];
    }
}
