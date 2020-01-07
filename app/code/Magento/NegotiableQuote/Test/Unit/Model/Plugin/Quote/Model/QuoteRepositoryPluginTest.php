<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Quote\Model;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Unit test for Magento\NegotiableQuote\Model\Plugin\Quote\Model\QuoteRepositoryPlugin class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRepositoryPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Quote\Model\QuoteRepositoryPlugin
     */
    private $quoteRepositoryPlugin;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItem;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemExtension;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributes;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Totals|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotals;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemResource;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->cartExtensionFactory = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->restriction = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteRepository = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteGrid = $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteItemFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'getNegotiableQuoteItem'])
            ->getMockForAbstractClass();
        $this->quoteItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'getTaxAmount',
                    'getTotalDiscountAmount',
                    'getBasePrice',
                    'getBaseTaxAmount',
                    'getChildren',
                    'isChildrenCalculated',
                    'getBaseDiscountAmount'
                ]
            )
            ->getMockForAbstractClass();
        $this->extensionFactory = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteTotalsFactory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\TotalsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteTotals = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\Totals::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItemExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setNegotiableQuoteItem'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteItemResource = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepositoryPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\Quote\Model\QuoteRepositoryPlugin::class,
            [
                'restriction' => $this->restriction,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'quoteGrid' => $this->quoteGrid,
                'negotiableQuoteItemFactory' => $this->negotiableQuoteItemFactory,
                'extensionFactory' => $this->extensionFactory,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                'negotiableQuoteItemResource' => $this->negotiableQuoteItemResource,
            ]
        );
    }

    /**
     * Test for method aroundSave with quoteId.
     *
     * @param bool $isChildrenCalculated
     * @param float|int $originalPrice
     * @param int $pricesCalls
     * @param string $quoteStatus
     * @return void
     * @dataProvider aroundSaveDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAroundSave($isChildrenCalculated, $originalPrice, $pricesCalls, $quoteStatus)
    {
        $quoteId = 1;
        $quoteItemId = 2;
        $quoteItemBasePrice = 100;
        $quoteItemBaseTax = 10;
        $baseDiscountAmount = 5;
        $originalTax = 20;
        $originalDiscountAmount = 15;

        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllItems'])
            ->getMockForAbstractClass();
        $proceed = function () use ($quote) {
            return $quote;
        };
        $negotiableQuote = $this->mockNegotiableQuote($quote);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getQuoteId')->willReturnOnConsecutiveCalls(null, $quoteId);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $quote->expects($this->once())->method('setIsActive')->with(false)->willReturnSelf();
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $negotiableQuote->expects($this->once())->method('setQuoteId')->with($quoteId)->willReturnSelf();
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('save')->with($negotiableQuote)->willReturn($negotiableQuote);
        $quote->expects($this->once())->method('getAllItems')->willReturn([$this->quoteItem]);
        $negotiableQuoteItem = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuoteItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteItem->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuoteItem')->willReturn($negotiableQuoteItem);
        $this->negotiableQuoteItemFactory->expects($this->exactly($pricesCalls))
            ->method('create')->willReturn($negotiableQuoteItem);
        $this->quoteItem->expects($this->atLeastOnce())->method('getItemId')->willReturn($quoteItemId);
        $negotiableQuoteItem->expects($this->exactly($pricesCalls))
            ->method('load')->with($quoteItemId)->willReturn($negotiableQuoteItem);
        $negotiableQuoteItem->expects($this->once())->method('setItemId')->with($quoteItemId)->willReturnSelf();
        $negotiableQuoteItem->expects($this->atLeastOnce())->method('getOriginalPrice')->willReturn($originalPrice);
        $this->quoteItem->expects($this->exactly($originalPrice ? 0 : 3))->method('getQty')->willReturn(1);
        $this->quoteItem->expects($this->exactly($pricesCalls))
            ->method('getBasePrice')->willReturn($quoteItemBasePrice);
        $this->quoteItem->expects($this->exactly($pricesCalls))
            ->method('getBaseTaxAmount')->willReturn($quoteItemBaseTax);
        $childItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseDiscountAmount'])
            ->getMockForAbstractClass();
        $this->quoteItem->expects($this->exactly($pricesCalls))->method('getChildren')->willReturn([$childItem]);
        $this->quoteItem->expects($this->exactly($pricesCalls))
            ->method('isChildrenCalculated')->willReturn($isChildrenCalculated);
        $childItem->expects($this->exactly(!$originalPrice && $isChildrenCalculated ? 1 : 0))
            ->method('getBaseDiscountAmount')->willReturn($baseDiscountAmount);
        $this->quoteItem->expects($this->exactly($isChildrenCalculated ? 0 : 1))
            ->method('getBaseDiscountAmount')->willReturn($baseDiscountAmount);
        $negotiableQuoteItem->expects($this->exactly($pricesCalls))
            ->method('getOriginalTaxAmount')->willReturn($originalTax);
        $negotiableQuoteItem->expects($this->exactly($pricesCalls))
            ->method('getOriginalDiscountAmount')->willReturn($originalDiscountAmount);
        $negotiableQuoteItem->expects($this->exactly($pricesCalls))
            ->method('setOriginalPrice')->with(null)->willReturnSelf();
        $negotiableQuoteItem->expects($this->exactly($pricesCalls))
            ->method('setOriginalTaxAmount')->with($originalTax)->willReturnSelf();
        $negotiableQuoteItem->expects($this->exactly($pricesCalls))
            ->method('setOriginalDiscountAmount')->with($originalDiscountAmount)->willReturnSelf();
        $this->extensionFactory->expects($this->exactly($pricesCalls))
            ->method('create')->willReturn($this->cartItemExtension);
        $this->cartItemExtension->expects($this->exactly($pricesCalls))
            ->method('setNegotiableQuoteItem')->with($negotiableQuoteItem)->willReturnSelf();
        $this->quoteItem->expects($this->exactly($pricesCalls))->method('setExtensionAttributes')
            ->with($this->cartItemExtension)->willReturnSelf();
        $this->quoteTotalsFactory->expects($this->once())
            ->method('create')
            ->with(['quote' => $quote])
            ->willReturn($this->quoteTotals);
        $negotiableQuote->expects($this->once())->method('getStatus')->willReturn($quoteStatus);
        if ($quoteStatus == NegotiableQuoteInterface::STATUS_CREATED) {
            $this->quoteTotals->expects($this->exactly(4))
                ->method('getCatalogTotalPrice')
                ->withConsecutive([true], [false], [false], [true])
                ->willReturn($originalPrice);
        } else {
            $this->quoteTotals->expects($this->exactly(2))
                ->method('getCatalogTotalPrice')
                ->withConsecutive([true], [false])
                ->willReturn($originalPrice);
            $this->quoteTotals->expects($this->exactly(2))
                ->method('getSubtotal')
                ->withConsecutive([false], [true])
                ->willReturn($originalPrice);
        }
        $negotiableQuote->expects($this->exactly(4))->method('setData')->willReturnSelf();
        $this->negotiableQuoteItemResource->expects($this->once())->method('saveList')->with([$negotiableQuoteItem]);
        $this->quoteGrid->expects($this->once())->method('refresh')->with($quote)->willReturnSelf();
        $this->quoteRepositoryPlugin->aroundSave($subject, $proceed, $quote);
    }

    /**
     * Test for method aroundGetActive without extensionAttributes.
     *
     * @return void
     */
    public function testAroundGetActiveWithoutAttributes()
    {
        $cartId = 42;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject->expects($this->once())->method('get')->with($cartId, [])->willReturn($quote);
        $quote->expects($this->once())->method('getExtensionAttributes')->willReturn(null);
        $proceed = function () {
            return null;
        };
        $this->assertNull($this->quoteRepositoryPlugin->aroundGetActive($subject, $proceed, $cartId));
    }

    /**
     * Test for method aroundGetActive with extensionAttributes.
     *
     * @return void
     */
    public function testAroundGetActiveWithAttributes()
    {
        $cartId = 42;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject->expects($this->once())->method('get')->willReturn($quote);
        $negotiableQuote = $this->mockNegotiableQuote($quote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);

        $proceed = function () {
            return null;
        };
        $this->assertEquals($quote, $this->quoteRepositoryPlugin->aroundGetActive($subject, $proceed, $cartId));
    }

    /**
     * Data provider for testAroundSave.
     *
     * @return array
     */
    public function aroundSaveDataProvider()
    {
        return [
            [true, null, 1, NegotiableQuoteInterface::STATUS_CREATED],
            [true, 10, 0, NegotiableQuoteInterface::STATUS_CREATED],
            [false, null, 1, NegotiableQuoteInterface::STATUS_CLOSED],
        ];
    }

    /**
     * Get mock for negotiable quote.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $quote
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockNegotiableQuote(\PHPUnit_Framework_MockObject_MockObject $quote)
    {
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($this->extensionAttributes);
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')->willReturn($negotiableQuote);
        return $negotiableQuote;
    }
}
