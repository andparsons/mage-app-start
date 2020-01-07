<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\PrintQuote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class NegotiationTest
 */
class NegotiationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\PrintQuote\Negotiation
     */
    protected $negotiation;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->negotiation = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\PrintQuote\Negotiation::class,
            []
        );
    }

    /**
     * Tests getTotalOptions() method
     *
     * @dataProvider getTotalOptionsDataProvider
     * @param int $type
     * @param string $expectedValue
     * @return void
     */
    public function testGetTotalOptions($type, $expectedValue)
    {
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layout->expects($this->any())->method('getParentName')->will($this->returnValue('parent'));
        $parent = $this->createMock(\Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals::class);
        $totals = [
            'negotiation' => new \Magento\Framework\DataObject(
                [
                    'code' => NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE,
                    'code_value' => NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE,
                    'value' => 5,
                    'type' => $type
                ]
            ),
            'catalog_price' => new \Magento\Framework\DataObject(
                [
                    'value' => 20,
                ]
            ),
        ];
        $parent->expects($this->any())->method('getTotals')->willReturn($totals);
        $layout->expects($this->any())->method('getBlock')->willReturn($parent);

        $this->negotiation->setLayout($layout);
        $totals = $this->negotiation->getTotalOptions();
        $this->assertEquals($expectedValue, $totals['proposed']->getValue());
    }

    /**
     * Data provider for testGetTotalOptions
     *
     * @return array
     */
    public function getTotalOptionsDataProvider()
    {
        return [
            [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT, 19],
            [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT, 15],
            [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL, 5],
        ];
    }
}
