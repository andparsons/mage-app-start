<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteItemManagement;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\PriceChecker;
use Magento\NegotiableQuote\Model\RuleChecker;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Model\QuoteUpdater;
use Magento\NegotiableQuote\Model\NegotiableQuote;
use Magento\NegotiableQuote\Model\Discount\StateChanges\Provider;
use Magento\NegotiableQuote\Model\Discount\StateChanges\Applier;
use Magento\NegotiableQuote\Model\QuoteItemsUpdater;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class QuoteUpdaterTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepository;

    /**
     * @var NegotiableQuoteItemManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemManagement;

    /**
     * @var PriceChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceChecker;

    /**
     * @var RuleChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleChecker;

    /**
     * @var RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $restriction;

    /**
     * @var QuoteUpdater
     */
    protected $quoteUpdater;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var NegotiableQuote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteNegotiation;

    /**
     * @var Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageProvider;

    /**
     * @var Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageApplier;

    /**
     * @var QuoteItemsUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemsUpdater;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quote = $this->getQuote();
        $this->quoteNegotiation = $this->getMockBuilder(NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getIsRegularQuote',
                    'setStatus',
                    'getStatus',
                    'getShippingPrice',
                    'getNegotiatedPriceValue',
                ]
            )
            ->getMock();
        $this->quoteNegotiation->expects($this->any())
            ->method('getIsRegularQuote')
            ->willReturn(true);
        $this->quoteNegotiation->expects($this->any())
            ->method('setStatus')
            ->willReturnSelf();
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->quoteRepository->expects($this->any())
            ->method('get')
            ->willReturn($this->quote);
        $this->quoteItemManagement = $this->createMock(NegotiableQuoteItemManagement::class);
        $this->restriction = $this->createMock(RestrictionInterface::class);
        $this->messageProvider = $this->createPartialMock(
            Provider::class,
            ['getChangesMessages']
        );
        $this->messageProvider->expects($this->any())
            ->method('getChangesMessages')
            ->willReturn(['test']);
        $this->messageApplier = $this->createMock(Applier::class);
        $this->quote->expects($this->any())
            ->method('collectTotals')
            ->willReturnSelf();
        $this->priceChecker = $this->getPriceChecker();
        $this->ruleChecker = $this->createMock(RuleChecker::class);
        $this->quoteItemsUpdater = $this->createPartialMock(
            QuoteItemsUpdater::class,
            ['updateItemsForQuote', 'hasUnconfirmedChanges', 'updateQuoteItemsByCartData']
        );

        $objectManager = new ObjectManager($this);
        $this->quoteUpdater = $objectManager->getObject(
            QuoteUpdater::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $this->restriction,
                'quoteItemManagement' => $this->quoteItemManagement,
                'ruleChecker' => $this->ruleChecker,
                'priceChecker' => $this->priceChecker,
                'messageProvider' => $this->messageProvider,
                'messageApplier' => $this->messageApplier,
                'quoteItemsUpdater' => $this->quoteItemsUpdater
            ]
        );
    }

    /**
     * Get quote mock
     *
     * @return Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuote()
    {
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->setMethods(
                [
                    'getExtensionAttributes',
                    'getShippingAddress',
                    'collectTotals',
                    'removeAllAddresses',
                    'getBillingAddress',
                    'removeAllItems',
                    'getItemsCollection',
                    'getAllVisibleItems',
                    'setData',
                    'getData',
                    'getItemById',
                    'removeItem',
                    'getAllAddresses',
                    'getAppliedRuleIds',
                    'setUpdatedAt',
                    'getId'
                ]
            )
            ->getMock();
        $itemsCollection = [];
        $quote->expects($this->any())
            ->method('getItemsCollection')
            ->willReturn($itemsCollection);

        return $quote;
    }

    /**
     * Get priceChecker mock
     *
     * @return PriceChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPriceChecker()
    {
        $priceChecker = $this->createPartialMock(
            PriceChecker::class,
            [
                'collectItemsPriceData',
                'getTotalDiscount',
                'checkIsProductPriceChanged',
                'checkIsDiscountChanged',
                'getQuoteMessages',
                'collectItemsCartPriceData',
                'checkIsCartPriceChanged',
                'setIsProductPriceChanged'
            ]
        );
        $priceChecker->expects($this->any())
            ->method('getTotalDiscount')
            ->willReturn([]);
        $priceChecker->expects($this->any())
            ->method('getQuoteMessages')
            ->willReturn([]);
        $priceChecker->expects($this->any())
            ->method('collectItemsPriceData')
            ->willReturn([]);
        $priceChecker->expects($this->any())
            ->method('collectItemsCartPriceData')
            ->willReturn([]);
        $priceChecker->expects($this->any())
            ->method('setIsProductPriceChanged')
            ->willReturn(true);

        return $priceChecker;
    }

    /**
     * Mock extension attributes
     *
     * @return CartExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockExtensionAttributes()
    {
        $extensionAttributes = $this->getMockBuilder(CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getNegotiableQuote',
                'setNegotiableQuote',
                'getShippingAssignments'
            ])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->any())
            ->method('getNegotiableQuote')
            ->willReturn($this->quoteNegotiation);
        $this->quote->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        return $extensionAttributes;
    }

    /**
     * Test for updateQuoteRestriction
     *
     * @return void
     */
    public function testUpdateQuoteRestriction()
    {
        $this->restriction->expects($this->any())
            ->method('canSubmit')
            ->willReturn(false);
        $this->mockExtensionAttributes();

        $this->assertEquals(false, $this->quoteUpdater->updateQuote(1, []));
    }

    /**
     * Test for updateQuote() method
     *
     * @dataProvider updateQuoteDataProvider
     * @param array $data
     * @param float|null $shippingPrice
     * @param float|null $negotiatedPrice
     * @param $expect
     * @return void
     */
    public function testUpdateQuote(array $data, $shippingPrice, $negotiatedPrice, $expect)
    {
        $this->restriction->expects($this->any())
            ->method('canSubmit')
            ->willReturn(true);
        $this->mockExtensionAttributes();
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getPostcode',
                    'getShippingMethod',
                ]
            )
            ->getMock();
        $address->expects($this->any())
            ->method('getPostcode')
            ->willReturn('111');
        $address->expects($this->any())
            ->method('getShippingMethod')
            ->willReturn('free');
        $this->quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($address);
        $this->quoteItemManagement->expects($this->any())
            ->method('updateQuoteItemsCustomPrices');
        $this->quoteNegotiation->expects($this->any())
            ->method('getShippingPrice')
            ->willReturn($shippingPrice);
        $this->quoteNegotiation->expects($this->any())
            ->method('getNegotiatedPriceValue')
            ->willReturn($negotiatedPrice);

        $this->assertEquals($expect, $this->quoteUpdater->updateQuote(1, $data));
    }

    /**
     * Test for updateQuote() method with recalculation
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateQuoteWithRecalculation()
    {
        $data = [];

        $this->restriction->expects($this->any())
            ->method('canSubmit')
            ->willReturn(true);
        $this->quote->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->getMockForAbstractClass();
        $extensionAttributesMock = $this->mockExtensionAttributes();
        $extensionAttributesMock->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $this->quote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesMock);
        $this->quoteItemManagement->expects($this->atLeastOnce())
            ->method('recalculateOriginalPriceTax')
            ->with(1, false, true, false, false);

        $this->assertEquals(false, $this->quoteUpdater->updateQuote(1, $data));
    }

    /**
     * Test for updateQuote() method with shipping method update.
     *
     * @return void
     */
    public function testUpdateQuoteWithNewShipping()
    {
        $data = ['shippingMethod' => 'test'];

        $this->restriction->expects($this->atLeastOnce())
            ->method('canSubmit')
            ->willReturn(true);
        $extensionAttributesMock = $this->mockExtensionAttributes();
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getPostcode',
                'getShippingMethod',
             ])
            ->getMock();
        $address->expects($this->atLeastOnce())
            ->method('getPostcode')
            ->willReturn('111');
        $address->expects($this->atLeastOnce())
            ->method('getShippingMethod')
            ->willReturn('dummy_shipping_method');
        $shippingAssignmentsMock = $this->getMockBuilder(ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributesMock->expects($this->atLeastOnce())
            ->method('getShippingAssignments')
            ->willReturn([$shippingAssignmentsMock]);
        $shippingMock = $this->getMockBuilder(ShippingInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $shippingAssignmentsMock->expects($this->atLeastOnce())
            ->method('getShipping')
            ->willReturn($shippingMock);
        $shippingMock->expects($this->any())
            ->method('setMethod')
            ->with($data['shippingMethod']);
        $this->quote->expects($this->atLeastOnce())
            ->method('getShippingAddress')
            ->willReturn($address);

        $this->assertEquals(true, $this->quoteUpdater->updateQuote(1, $data));
    }

    /**
     * DataProvider for testUpdateQuote()
     *
     * @return array
     */
    public function updateQuoteDataProvider()
    {
        return [
            [['proposed' => ['type' => 1, 'value' => ''], 'update' => 0], null, null, true],
            [['expiration_period' => 'test', 'update' => 0], null, null, true],
            [['shipping' => 10, 'update' => 0], null, null, true],
            [['shipping' => 15], null, null, true],
            [['shippingMethod' => 'test', 'update' => 1], 7.5, 15, true],
        ];
    }

    /**
     * Test for updateQuote() method with exception
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testUpdateQuoteWithException()
    {
        $this->quote->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->quoteUpdater->updateQuote(1, []);
    }

    /**
     * Test for updateItemsForQuote() method
     *
     * @return void
     */
    public function testUpdateQuoteItems()
    {
        $data = [
            'items' => [
                ['sku' => 1, 'qty' => 2, 'id' => 1],
            ],
            'update' => 0
        ];
        $this->restriction->expects($this->any())
            ->method('canSubmit')->willReturn(true);
        $this->mockExtensionAttributes();
        $productMock = $this->createPartialMock(Product::class, ['canConfigure']);
        $productMock->expects($this->any())
            ->method('canConfigure')
            ->willReturn(true);
        $item =
            $this->createPartialMock(Item::class, ['getProduct', 'canConfigure', 'setQty']);
        $item->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->quote->expects($this->any())
            ->method('getItemById')
            ->willReturn($item);
        $this->quote->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn([$item]);
        $this->quote->expects($this->any())
            ->method('getData')
            ->with('trigger_recollect')
            ->willReturn(true);
        $addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['unsetData'])
            ->getMock();
        $addressMock->expects($this->once())
            ->method('unsetData')
            ->with('cached_items_all');
        $this->quote->expects($this->any())
            ->method('getAllAddresses')
            ->willReturn([$addressMock]);
        $this->quoteItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax');
        $this->quoteItemsUpdater->expects($this->once())
            ->method('updateItemsForQuote')
            ->willReturn(true);

        $this->assertEquals(true, $this->quoteUpdater->updateQuote(1, $data));
    }

    /**
     * Test for updateCurrentDate() method
     *
     * @return void
     */
    public function testUpdateCurrentDate()
    {
        $this->quote->expects($this->once())
            ->method('setUpdatedAt')
            ->willReturnSelf();
        $this->assertEquals($this->quote, $this->quoteUpdater->updateCurrentDate($this->quote));
    }

    /**
     * Test for updateQuoteItemsByCartData() method
     *
     * @return void
     */
    public function testUpdateQuoteItemsByCartData()
    {
        $this->quoteItemsUpdater->expects($this->once())
            ->method('updateQuoteItemsByCartData')
            ->willReturn($this->quote);
        $this->assertEquals(
            $this->quote,
            $this->quoteUpdater->updateQuoteItemsByCartData($this->quote, [])
        );
    }
}
