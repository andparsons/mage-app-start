<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Class QuoteItemsUpdaterTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteItemsUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteItemsUpdater
     */
    private $quoteItemsUpdater;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\Checkout\Model\CartFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuote;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quote = $this->getQuote();
        $this->cartMock = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\Cart::class,
            ['addConfigureditems', 'addItems', 'getDeletedItemsSku']
        );
        $this->negotiableQuoteHelper = $this->getNegotiableQuoteHelper();
        $this->quoteTotalsFactory =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\Quote\TotalsFactory::class, ['create']);
        $this->cartFactory = $this->createPartialMock(\Magento\Checkout\Model\CartFactory::class, ['create']);

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $this->negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getNegotiatedPriceValue',
                    'setIsCustomerPriceChanged',
                    'getDeletedSku',
                    'setDeletedSku'
                ]
            )
            ->getMock();
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')->willReturn($this->negotiableQuote);
        $this->quote->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteItemsUpdater = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\QuoteItemsUpdater::class,
            [
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                'cartFactory' => $this->cartFactory,
                'cart' => $this->cartMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Get negotiableQuoteHelper mock.
     *
     * @return \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getNegotiableQuoteHelper()
    {
        $negotiableQuoteHelper = $this->createPartialMock(
            \Magento\NegotiableQuote\Helper\Quote::class,
            ['setHasChangesInNegotiableQuote', 'retrieveCustomOptions']
        );
        $negotiableQuoteHelper->expects($this->any())
            ->method('setHasChangesInNegotiableQuote')
            ->willReturnSelf();
        $negotiableQuoteHelper->expects($this->any())
            ->method('retrieveCustomOptions')
            ->willReturn(['test']);

        return $negotiableQuoteHelper;
    }

    /**
     * Get quote mock.
     *
     * @return \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuote()
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()
            ->setMethods(
                [
                    'removeAllItems',
                    'getItemsCollection',
                    'getAllVisibleItems',
                    'setData',
                    'getData',
                    'getItemById',
                    'removeItem',
                    'getExtensionAttributes'
                ]
            )
            ->getMock();
        $itemsCollection = [];
        $quote->expects($this->any())->method('getItemsCollection')->willReturn($itemsCollection);

        return $quote;
    }

    /**
     * Test for updateItemsForQuote() method.
     *
     * @dataProvider updateQuoteItemsTestDataProvider
     * @param array $data
     * @param string $deletedSku
     * @param array $failedSku
     * @param string $deletedSkuResult
     * @param bool $canConfigure
     * @param bool $expect
     * @return void
     */
    public function testUpdateItemsForQuote(
        array $data,
        $deletedSku,
        array $failedSku,
        $deletedSkuResult,
        $canConfigure,
        $expect
    ) {
        $this->cartMock->expects($this->any())
            ->method('addConfiguredItems')
            ->willReturn(true);
        $this->cartMock->expects($this->any())
            ->method('addItems')
            ->willReturn(true);
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['canConfigure']);
        $productMock->expects($this->any())->method('canConfigure')->willReturn($canConfigure);
        $item = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'canConfigure', 'setQty']
        );
        $item->expects($this->any())->method('getProduct')->willReturn($productMock);
        $this->quote->expects($this->any())->method('getItemById')->will($this->returnValue($item));
        $this->quote->expects($this->any())->method('getAllVisibleItems')->will($this->returnValue([$item]));
        $this->cartMock->expects($this->once())->method('getDeletedItemsSku')->willReturn($failedSku);
        $this->negotiableQuote->expects(count($failedSku) ? $this->once() : $this->never())
            ->method('getDeletedSku')->willReturn($deletedSku);
        $this->negotiableQuote->expects(count($failedSku) ? $this->once() : $this->never())
            ->method('setDeletedSku')->with($deletedSkuResult)->willReturnSelf();

        $this->assertEquals($expect, $this->quoteItemsUpdater->updateItemsForQuote($this->quote, $data, true));
    }

    /**
     * DataProvider for testUpdateItemsForQuote.
     *
     * @return array
     */
    public function updateQuoteItemsTestDataProvider()
    {
        return [
            [
                [
                    'items' => [
                        ['sku' => 1, 'qty' => 2, 'id' => 1],
                        ['sku' => 2, 'qty' => 1, 'id' => 2]
                    ],
                    'update' => 1
                ],
                json_encode([
                    \Magento\Framework\App\Area::AREA_ADMINHTML => [3],
                    \Magento\Framework\App\Area::AREA_FRONTEND => []
                ]),
                [2],
                json_encode([
                    \Magento\Framework\App\Area::AREA_ADMINHTML => [3, 2],
                    \Magento\Framework\App\Area::AREA_FRONTEND => []
                ]),
                false,
                true
            ],
            [
                [
                    'items' => [
                        ['sku' => 1, 'qty' => 2, 'id' => 1, 'productSku' => 'test', 'config' => 'test'],
                        ['sku' => 2, 'qty' => 1, 'id' => 2, 'productSku' => 'test2', 'config' => 'test2'],
                    ],
                    'update' => 0
                ],
                '',
                [2],
                json_encode([
                    \Magento\Framework\App\Area::AREA_ADMINHTML => [2],
                    \Magento\Framework\App\Area::AREA_FRONTEND => []
                ]),
                true,
                true
            ],
            [
                [
                    'items' => [
                        ['sku' => 1, 'qty' => null, 'id' => null, 'productSku' => 'test', 'config' => 'test'],
                    ],
                    'update' => 0
                ],
                '',
                [],
                '',
                false,
                true
            ],
            [
                [
                    'items' => [
                        ['sku' => 1, 'qty' => null, 'id' => null, 'productSku' => 'test', 'config' => 'test'],
                    ],
                    'update' => 0
                ],
                '',
                [],
                '',
                true,
                true
            ],
            [
                [
                    'items' => [
                        ['sku' => 1, 'qty' => null, 'id' => null, 'productSku' => 'test', 'config' => null],
                    ],
                    'update' => 0
                ],
                '',
                [],
                '',
                false,
                true
            ],
            [
                [
                    'addItems' => [
                        ['sku' => 1, 'qty' => 2, 'id' => null],
                        ['sku' => 2, 'qty' => 3, 'id' => null],
                    ],
                    'update' => 1
                ],
                '',
                [],
                '',
                false,
                true
            ],
        ];
    }

    /**
     * Test for updateQuoteItemsByCartData() method.
     *
     * @return void
     */
    public function testUpdateQuoteItemsByCartData()
    {
        $totals = $this->createMock(\Magento\NegotiableQuote\Model\Quote\Totals::class);
        $totals->expects($this->any())->method('getCatalogTotalPrice')->willReturnOnConsecutiveCalls(22, 20);
        $cart = $this->createMock(\Magento\Checkout\Model\Cart::class);
        $this->quoteTotalsFactory->expects($this->once())->method('create')->willReturn($totals);
        $this->cartFactory->expects($this->once())->method('create')->willReturn($cart);
        $cart->expects($this->any())->method('getQuote')->willReturn($this->quote);
        $this->negotiableQuote->expects($this->atLeastOnce())->method('getNegotiatedPriceValue')->willReturn(17);
        $this->negotiableQuote->expects($this->once())
            ->method('setIsCustomerPriceChanged')->with(true)->willReturnSelf();

        $this->assertEquals(
            $this->quote,
            $this->quoteItemsUpdater->updateQuoteItemsByCartData($this->quote, [['qty' => 1]])
        );
    }
}
