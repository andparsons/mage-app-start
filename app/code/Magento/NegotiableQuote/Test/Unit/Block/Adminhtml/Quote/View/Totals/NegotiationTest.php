<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View\Totals;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class NegotiationTest.
 */
class NegotiationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataHelper;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals\Negotiation
     */
    private $negotiation;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->postDataHelper = $this->createMock(\Magento\Framework\Data\Helper\PostHelper::class);
        $this->negotiableQuoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);
        $this->priceCurrency = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->restriction = $this->createMock(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        );

        $this->negotiation = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals\Negotiation::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'postDataHelper' => $this->postDataHelper,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'priceCurrency' => $this->priceCurrency,
                'restriction' => $this->restriction,
                'data' => []
            ]
        );
    }

    /**
     * Test getTotalOptions.
     *
     * @param int $type
     * @param string $expectType
     * @return void
     * @dataProvider getTotalOptionsDataProvider
     */
    public function testGetTotalOptions($type, $expectType)
    {
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layout->expects($this->any())->method('getParentName')->will($this->returnValue('parent'));
        $parent = $this->createMock(\Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals::class);
        $total = new \Magento\Framework\DataObject(
            [
                'code' => NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE,
                'code_value' => NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE,
                'value' => 10,
                'type' => $type
            ]
        );
        $parent->expects($this->any())->method('getTotals')->willReturn(['negotiation' => $total]);
        $layout->expects($this->any())->method('getBlock')->will($this->returnValue($parent));
        $this->negotiation->setLayout($layout);

        $totals = $this->negotiation->getTotalOptions();

        $this->assertEquals($totals[$expectType]->getValue(), 10);
    }

    /**
     * Data provider for testGetTotalOptions.
     *
     * @return array
     */
    public function getTotalOptionsDataProvider()
    {
        return [
            [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT, 'percentage'],
            [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL, 'proposed'],
        ];
    }

    /**
     * Test getCatalogPrice.
     *
     * @return void
     */
    public function testGetCatalogPrice()
    {
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layout->expects($this->any())->method('getParentName')->will($this->returnValue('parent'));
        $parent = $this->createMock(\Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals::class);
        $total = new \Magento\Framework\DataObject(
            [
                'value' => 50
            ]
        );
        $parent->expects($this->any())->method('getTotals')->willReturn(['catalog_price' => $total]);
        $layout->expects($this->any())->method('getBlock')->will($this->returnValue($parent));
        $this->negotiation->setLayout($layout);
        $this->assertEquals(50, $this->negotiation->getCatalogPrice());
    }

    /**
     * Test displayPrices.
     *
     * @return void
     */
    public function testDisplayPrices()
    {
        $this->assertSame('10.00', $this->negotiation->displayPrices(10));
    }
}
