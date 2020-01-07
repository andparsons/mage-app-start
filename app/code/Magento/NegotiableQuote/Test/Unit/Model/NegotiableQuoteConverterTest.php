<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Test for NegotiableQuoteConverter class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NegotiableQuoteConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\Data\CartInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterfaceFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteConverter
     */
    private $negotiableQuoteConverter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->cartFactory = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productFactory = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->extensionFactory = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->cartItemFactory = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteItemFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->negotiableQuoteConverter = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\NegotiableQuoteConverter::class,
            [
                'cartFactory' => $this->cartFactory,
                'productFactory' => $this->productFactory,
                'productRepository' => $this->productRepository,
                'extensionFactory' => $this->extensionFactory,
                'cartItemFactory' => $this->cartItemFactory,
                'negotiableQuoteItemFactory' => $this->negotiableQuoteItemFactory,
                'negotiableQuoteFactory' => $this->negotiableQuoteFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder
            ]
        );
    }

    /**
     * Test for quoteToArray method.
     *
     * @return void
     */
    public function testQuoteToArray()
    {
        $quoteData = [
            'quote_id' => 1,
        ];
        $negotiableQuoteData = [
            'snapshot' => [],
            'items' => [],
        ];
        $addressData = ['city' => 'New York'];
        $itemData = ['item_id' => 10];
        $itemOptionData = ['value' => 'option value'];
        $productData = ['name' => 'product name'];
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getShippingAddress', 'getBillingAddress', 'getItemsCollection'])
            ->getMockForAbstractClass();
        $quote->expects($this->once())->method('getData')->willReturn($quoteData);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $quote->expects($this->once())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $quoteExtensionAttributes->expects($this->once())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getData')->willReturn($negotiableQuoteData);
        $address = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($address);
        $quote->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($address);
        $address->expects($this->atLeastOnce())->method('getData')->willReturn($addressData);
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getOptions'])
            ->getMockForAbstractClass();
        $quote->expects($this->once())->method('getItemsCollection')->willReturn([$quoteItem]);
        $quoteItem->expects($this->once())->method('getData')->willReturn($itemData);
        $quoteItemExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuoteItem'])
            ->getMockForAbstractClass();
        $negotiableQuoteItem = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $quoteItem->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($quoteItemExtensionAttributes);
        $quoteItemExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuoteItem')->willReturn($negotiableQuoteItem);
        $negotiableQuoteItem->expects($this->once())->method('getData')->willReturn($itemData);
        $quoteItemOption = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getProduct'])
            ->getMockForAbstractClass();
        $quoteItem->expects($this->once())->method('getOptions')->willReturn([$quoteItemOption]);
        $quoteItemOption->expects($this->once())->method('getData')->willReturn($itemOptionData);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $quoteItemOption->expects($this->exactly(2))->method('getProduct')->willReturn($product);
        $product->expects($this->once())->method('getData')->willReturn($productData);
        $this->assertEquals(
            [
                'quote' => $quoteData,
                'negotiable_quote' => [],
                'shipping_address' => $addressData,
                'billing_address' => $addressData,
                'items' => [
                    $itemData +
                    [
                        'negotiable_quote_item' => $itemData,
                        'options' => [$itemOptionData + ['product' => $productData]],
                    ]
                ],
            ],
            $this->negotiableQuoteConverter->quoteToArray($quote)
        );
    }

    /**
     * Test for arrayToQuote method.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testArrayToQuote()
    {
        $data = [
            'quote' => ['quote_id' => 1],
            'shipping_address' => ['city' => 'New York'],
            'billing_address' => ['city' => 'Chicago'],
            'negotiable_quote' => [],
            'items' => [
                [
                    'product_id' => 20,
                    'negotiable_quote_item' => ['item_id' => 30],
                    'options' => [
                        [
                            'product' => ['entity_id' => 20],
                            'value' => 'option_value'
                        ],
                    ],
                ],
                ['product_id' => 21],
            ]
        ];
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setData',
                    'removeAllAddresses',
                    'getShippingAddress',
                    'getBillingAddress',
                    'removeAllItems',
                    'getItemsCollection',
                    'setTotalsCollectedFlag',
                    'setExtensionAttributes'
                ]
            )
            ->getMock();
        $this->cartFactory->expects($this->once())->method('create')->willReturn($quote);
        $quote->expects($this->once())->method('setData')->with($data['quote'])->willReturnSelf();
        $quote->expects($this->once())->method('removeAllAddresses')->willReturnSelf();
        $address = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $quote->expects($this->once())->method('getShippingAddress')->willReturn($address);
        $quote->expects($this->once())->method('getBillingAddress')->willReturn($address);
        $address->expects($this->atLeastOnce())->method('setData')
            ->withConsecutive([$data['shipping_address']], [$data['billing_address']])->willReturnSelf();
        $quote->expects($this->once())->method('removeAllItems')->willReturnSelf();
        $itemsCollection = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Item\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'removeAllItems', 'addItem', 'getItems'])
            ->getMock();
        $quote->expects($this->once())->method('getItemsCollection')->willReturn($itemsCollection);
        $itemsCollection->expects($this->once())->method('removeAllItems')->willReturnSelf();
        $this->filterBuilder->expects($this->at(0))->method('setField')->with('entity_id')->willReturnSelf();
        $this->filterBuilder->expects($this->at(1))->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilder->expects($this->at(2))
            ->method('setValue')->with($data['items'][0]['product_id'])->willReturnSelf();
        $this->filterBuilder->expects($this->at(4))->method('setField')->with('entity_id')->willReturnSelf();
        $this->filterBuilder->expects($this->at(5))->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilder->expects($this->at(6))
            ->method('setValue')->with($data['items'][1]['product_id'])->willReturnSelf();
        $filter1 = $this->createMock(\Magento\Framework\Api\Filter::class);
        $filter2 = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilder->expects($this->atLeastOnce())
            ->method('create')->willReturnOnConsecutiveCalls($filter1, $filter2);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilters')->with([$filter1, $filter2])->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(1);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $searchResults->expects($this->once())->method('getItems')->willReturn([$product]);
        $product->expects($this->once())->method('getId')->willReturn($data['items'][0]['product_id']);
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'setQuote', 'addOption', 'setExtensionAttributes'])
            ->getMock();
        $this->cartItemFactory->expects($this->once())->method('create')->willReturn($quoteItem);
        $quoteItem->expects($this->once())
            ->method('setData')->with(['product_id' => $data['items'][0]['product_id']])->willReturnSelf();
        $quoteItem->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->productFactory->expects($this->once())->method('create')->willReturn($product);
        $product->expects($this->once())
            ->method('setData')->with($data['items'][0]['options'][0]['product'])->willReturnSelf();
        $quoteItem->expects($this->once())->method('addOption')->willReturnSelf();
        $negotiableQuoteItem = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteItemFactory->expects($this->once())->method('create')->willReturn($negotiableQuoteItem);
        $negotiableQuoteItem->expects($this->once())->method('setData')->with()->willReturnSelf();
        $quoteItemExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setNegotiableQuoteItem'])
            ->getMockForAbstractClass();
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setNegotiableQuote'])
            ->getMockForAbstractClass();
        $this->extensionFactory->expects($this->atLeastOnce())->method('create')
            ->withConsecutive([get_class($quoteItem)], [get_class($quote)])
            ->willReturnOnConsecutiveCalls($quoteItemExtensionAttributes, $quoteExtensionAttributes);
        $quoteItemExtensionAttributes->expects($this->once())
            ->method('setNegotiableQuoteItem')->with($negotiableQuoteItem)->willReturnSelf();
        $quoteItem->expects($this->once())
            ->method('setExtensionAttributes')->with($quoteItemExtensionAttributes)->willReturnSelf();
        $itemsCollection->expects($this->once())->method('addItem')->with($quoteItem)->willReturnSelf();
        $itemsCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$quoteItem]);
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteFactory->expects($this->once())->method('create')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('setData')->with($data['negotiable_quote'])->willReturnSelf();
        $quoteExtensionAttributes->expects($this->once())
            ->method('setNegotiableQuote')->with($negotiableQuote)->willReturnSelf();
        $quote->expects($this->once())
            ->method('setExtensionAttributes')->with($quoteExtensionAttributes)->willReturnSelf();
        $quote->expects($this->once())->method('setTotalsCollectedFlag')->with(false)->willReturnSelf();
        $this->assertEquals($quote, $this->negotiableQuoteConverter->arrayToQuote($data));
    }
}
