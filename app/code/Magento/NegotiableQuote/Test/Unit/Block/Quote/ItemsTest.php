<?php
namespace Magento\NegotiableQuote\Test\Unit\Block\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class ItemsTest
 * @package Magento\NegotiableQuote\Test\Unit\Block\Quote
 */
class ItemsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Items
     */
    protected $itemsBlock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItem;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface
     */
    private $negotiableQuote;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quoteItem =  $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->quote->expects($this->any())
            ->method('getAllVisibleItems')->will($this->returnValue([$this->quoteItem]));

        $this->negotiableQuoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);
        $this->negotiableQuoteHelper->expects($this->any())->method('resolveCurrentQuote')
            ->will($this->returnValue($this->quote));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemsBlock = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\Items::class,
            [
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'data' => []
            ]
        );

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->itemsBlock->setLayout($layout);
    }

    /**
     * Test for method getItems()
     *
     * @return void
     */
    public function testGetItems()
    {
        $this->assertEquals([$this->quoteItem], $this->itemsBlock->getItems());
    }

    /**
     * @dataProvider dataForItemHtml
     *
     * @param string $productType
     * @param string $listName
     * @param bool $isRenderer
     * @param bool $expectedResult
     */
    public function testGetItemHtml($productType, $listName, $isRenderer, $expectedResult)
    {
        $this->quoteItem->expects($this->any())
            ->method('getProductType')->will($this->returnValue($productType));
        $this->itemsBlock->setRendererListName($listName);

        $rendererList =  null;
        if ($isRenderer) {
            $rendererList = $this->createPartialMock(
                \Magento\Framework\View\Element\RendererList::class,
                ['getRenderer']
            );
            $renderer = $this->createPartialMock(
                \Magento\Framework\View\Element\Template::class,
                ['setItem', 'toHtml']
            );
            $renderer->expects($this->any())
                ->method('toHtml')->will($this->returnValue('rendered'));
            $renderer->expects($this->any())
                ->method('setItem')->will($this->returnSelf());
            $rendererList->expects($this->any())
                ->method('getRenderer')->will($this->returnValue($renderer));
        }
        if ($listName) {
            $this->itemsBlock->getLayout()->expects($this->any())
                ->method('getBlock')->with($listName)->will($this->returnValue($rendererList));
        } else {
            $this->itemsBlock->getLayout()->expects($this->any())
                ->method('getChildName')->will($this->returnValue('child'));
            $this->itemsBlock->getLayout()->expects($this->any())
                ->method('getBlock')->with('child')->will($this->returnValue($rendererList));
        }

        try {
            $result = $this->itemsBlock->getItemHtml($this->quoteItem);
        } catch (\Exception $e) {
            $result = 'error';
        }

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return void
     */
    private function prepareQuote()
    {
        $this->negotiableQuote = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getNegotiatedPriceValue',
                'getStatus',
                'getShippingPrice',
                'getIsCustomerPriceChanged',
                'getIsShippingTaxChanged'
            ])
            ->getMockForAbstractClass();
        $extensionAttributes = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $this->quote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->any())
            ->method('getNegotiableQuote')
            ->willReturn($this->negotiableQuote);
         $this->negotiableQuote->expects($this->once())
            ->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN);
    }

    /**
     * @return void
     */
    public function testIsCustomerPriceChanged()
    {
        $this->prepareQuote();
        $this->negotiableQuote->expects($this->atLeastOnce())
            ->method('getNegotiatedPriceValue')
            ->willReturn(1);
        $this->negotiableQuote->expects($this->once())
            ->method('getIsCustomerPriceChanged')
            ->willReturn(true);
        $this->assertTrue($this->itemsBlock->isCustomerPriceChanged());
    }

    /**
     * @return void
     */
    public function testIsShippingTaxChanged()
    {
        $this->prepareQuote();
        $this->negotiableQuote->expects($this->atLeastOnce())
            ->method('getShippingPrice')
            ->willReturn(1);
        $this->negotiableQuote->expects($this->once())
            ->method('getIsShippingTaxChanged')
            ->willReturn(true);
        $this->assertTrue($this->itemsBlock->isShippingTaxChanged());
    }

    /**
     * Data provider for testGetItemHtml
     *
     * @return array
     */
    public function dataForItemHtml()
    {
        return [
            ['simple', 'renderer', false, 'error'],
            ['simple', 'renderer', true, 'rendered'],
            [null, null, true, 'rendered'],
        ];
    }
}
