<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

/**
 * Test for Magento\NegotiableQuote\Model\History\SnapshotInformationManagement class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SnapshotInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensibleDataObjectConverter;

    /**
     * @var \Magento\NegotiableQuote\Model\History\SnapshotInformationManagement
     */
    private $snapshotInformationManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->commentManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\CommentManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteTotalsFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Quote\TotalsFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensibleDataObjectConverter = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->snapshotInformationManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\History\SnapshotInformationManagement::class,
            [
                'commentManagement' => $this->commentManagement,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverter,
            ]
        );
    }

    /**
     * Test prepareSnapshotData method.
     *
     * @return void
     */
    public function testPrepareSnapshotData()
    {
        $expectedResult = [
            'expiration_date' => \Magento\NegotiableQuote\Model\Expiration::DATE_QUOTE_NEVER_EXPIRES,
            'price' => [
                'type' => 'sample_type',
                'value' => 'sample_value',
            ],
            'shipping' => [
                'method' => 'rate_carrier_title - rate_method_title',
                'price' => 5,
            ],
            'address' => ['id' => 1, 'array' => ['street' => ['street' => 'Street Name']]],
            'subtotal' => 7,
        ];
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAddress'])
            ->getMockForAbstractClass();
        $quoteExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $address = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getPostcode', 'getShippingMethod', 'getAllShippingRates', 'getCustomerAddressId', 'getStreet']
            )
            ->getMockForAbstractClass();
        $rate = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getCarrierTitle', 'getMethodTitle'])
            ->getMock();
        $quoteTotals = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\Totals::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtension);
        $quoteExtension->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getNegotiatedPriceType')->willReturn($expectedResult['price']['type']);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getNegotiatedPriceValue')->willReturn($expectedResult['price']['value']);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('getShippingPrice')->willReturn($expectedResult['shipping']['price']);
        $address->expects($this->atLeastOnce())->method('getPostcode')->willReturn('address_postcode');
        $rateCode = 'rate_code';
        $address->expects($this->atLeastOnce())->method('getShippingMethod')->will($this->returnCallback(
            function () use (&$rateCode) {
                return $rateCode;
            }
        ));
        $address->expects($this->atLeastOnce())
            ->method('getCustomerAddressId')
            ->willReturn($expectedResult['address']['id']);
        $address->expects($this->atLeastOnce())->method('getStreet')->willReturn(['street' => 'Street Name']);
        $this->extensibleDataObjectConverter->expects($this->atLeastOnce())
            ->method('toFlatArray')
            ->with($address, [], \Magento\Quote\Api\Data\AddressInterface::class)
            ->willReturn(['street' => 'Another Street Name']);
        $rate->expects($this->atLeastOnce())->method('getCode')->willReturn('rate_code');
        $rate->expects($this->atLeastOnce())->method('getCarrierTitle')->willReturn('rate_carrier_title');
        $rate->expects($this->atLeastOnce())->method('getMethodTitle')->willReturn('rate_method_title');

        $address->expects($this->atLeastOnce())->method('getAllShippingRates')->willReturn([$rate]);
        $quote->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($address);
        $quoteTotals->expects($this->any())->method('getSubtotal')->willReturn($expectedResult['subtotal']);
        $this->quoteTotalsFactory->expects($this->atLeastOnce())->method('create')->willReturn($quoteTotals);
        $this->assertEquals($expectedResult, $this->snapshotInformationManagement->prepareSnapshotData($quote, []));
        $expectedResult['expiration_date'] = 'sample_date';
        unset($expectedResult['shipping']);
        $rateCode = null;
        $negotiableQuote->expects($this->once())
            ->method('getExpirationPeriod')->willReturn($expectedResult['expiration_date']);
        $this->assertEquals($expectedResult, $this->snapshotInformationManagement->prepareSnapshotData($quote, []));
    }

    /**
     * Test collectCommentData method.
     *
     * @return void
     */
    public function testCollectCommentData()
    {
        $commentId = 1;
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commentCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $comment->expects($this->once())->method('getEntityId')->willReturn($commentId);
        $commentCollection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([$comment]));
        $this->commentManagement->expects($this->once())->method('getQuoteComments')->willReturn($commentCollection);

        $this->assertEquals([$commentId], $this->snapshotInformationManagement->collectCommentData(1));
    }

    /**
     * Test collectCartData method.
     *
     * @param array $data
     * @param array $options
     * @param array $expectedResult
     * @return void
     * @dataProvider collectCartDataDataProvider
     */
    public function testCollectCartData(array $data, array $options, array $expectedResult)
    {
        $cartItemCollection = $this->getMockBuilder(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator'])
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemsCollection'])
            ->getMockForAbstractClass();
        $quote->expects($this->once())->method('getItemsCollection')->willReturn($cartItemCollection);
        $items = [];
        foreach ($data as $itemId => $item) {
            $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['getBuyRequest', 'getProductId', 'getSku', 'getQty', 'getData', 'getItemId', 'getName'])
                ->getMockForAbstractClass();
            $cartItem->expects($this->atLeastOnce())
                ->method('getData')
                ->with('parent_item_id')
                ->willReturn($item['parent_item_id']);
            $cartItem->expects($this->atLeastOnce())->method('getBuyRequest')->willReturn(true);
            $cartItem->expects($this->atLeastOnce())->method('getProductId')->willReturn($item['product_id']);
            $cartItem->expects($this->atLeastOnce())->method('getSku')->willReturn($item['sku']);
            $cartItem->expects($this->atLeastOnce())->method('getQty')->willReturn($item['qty']);
            $cartItem->expects($this->atLeastOnce())->method('getItemId')->willReturn($itemId);
            $cartItem->expects($this->atLeastOnce())->method('getName')->willReturn('product1');
            $items[] = $cartItem;
        }
        $cartItemCollection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator($items));
        $this->negotiableQuoteHelper->expects($this->atLeastOnce())
            ->method('retrieveCustomOptions')
            ->with($cartItem, false)
            ->willReturn($options);

        $this->assertEquals($expectedResult, $this->snapshotInformationManagement->collectCartData($quote));
    }

    /**
     * Data provider for collectCartData method.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collectCartDataDataProvider()
    {
        return [
            [
                [
                    1 => [
                        'product_id' => 100,
                        'parent_item_id' => null,
                        'sku' => 'product100',
                        'qty' => 1,
                    ],
                    2 => [
                        'product_id' => 101,
                        'parent_item_id' => null,
                        'sku' => 'product101',
                        'qty' => 1,
                    ],
                ],
                [
                    'super_attribute' => [
                        'option_key' => 'option_value',
                    ]
                ],
                [
                    1 => [
                        'product_id' => 100,
                        'sku' => 'product100',
                        'qty' => 1,
                        'options' => [
                            [
                                'option' => 'option_key',
                                'value' => 'option_value',
                            ],
                        ],
                        'name' => 'product1'
                    ],
                    2 => [
                        'product_id' => 101,
                        'sku' => 'product101',
                        'qty' =>1,
                        'options' => [
                            [
                                'option' => 'option_key',
                                'value' => 'option_value',
                            ],
                        ],
                        'name' => 'product1'
                    ]
                ]

            ],
            [
                [
                    1 => [
                        'product_id' => 100,
                        'parent_item_id' => null,
                        'sku' => 'product100',
                        'qty' => 1,
                    ],
                    2 => [
                        'product_id' => 101,
                        'parent_item_id' => null,
                        'sku' => 'product101',
                        'qty' => 1,
                    ],
                ],
                [
                    'bundle_option' => [
                        'option_key' => 'option_value',
                    ]
                ],
                [
                    1 => [
                        'product_id' => 100,
                        'sku' => 'product100',
                        'qty' => 1,
                        'options' => [
                            [
                                'option' => 'option_key',
                                'value' => 'option_value',
                            ],
                        ],
                        'name' => 'product1'
                    ],
                    2 => [
                        'product_id' => 101,
                        'sku' => 'product101',
                        'qty' =>1,
                        'options' => [
                            [
                                'option' => 'option_key',
                                'value' => 'option_value',
                            ],
                        ],
                        'name' => 'product1'
                    ]
                ]

            ],
            [
                [
                    1 => [
                        'product_id' => 100,
                        'parent_item_id' => null,
                        'sku' => 'product100',
                        'qty' => 1,
                    ],
                    2 => [
                        'product_id' => 101,
                        'parent_item_id' => null,
                        'sku' => 'product101',
                        'qty' => 1,
                    ],
                ],
                [
                    'options' => [
                        'option_key' => 'option_value',
                    ]
                ],
                [
                    1 => [
                        'product_id' => 100,
                        'sku' => 'product100',
                        'qty' => 1,
                        'options' => [
                            [
                                'option' => 'option_key',
                                'value' => 'option_value',
                            ],
                        ],
                        'name' => 'product1'
                    ],
                    2 => [
                        'product_id' => 101,
                        'sku' => 'product101',
                        'qty' =>1,
                        'options' => [
                            [
                                'option' => 'option_key',
                                'value' => 'option_value',
                            ],
                        ],
                        'name' => 'product1'
                    ]
                ]

            ],
        ];
    }

    /**
     * Test prepareSystemLogData method.
     *
     * @return void
     */
    public function testPrepareSystemLogData()
    {
        $data = [
            'status' => [
                'new_value' => \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_EXPIRED,
            ],
            'negotiated_price' => 3,
            'shipping' => 2,
            'subtotal' => 5,
        ];

        $expectedResult = [
            'status' => $data['status'],
            'system_data' => array_filter(
                $data,
                function ($key) {
                    return $key != 'status';
                },
                ARRAY_FILTER_USE_KEY
            ) + ['check_system' => true],
        ];

        $this->assertEquals($expectedResult, $this->snapshotInformationManagement->prepareSystemLogData($data));
    }

    /**
     * Test prepareSystemLogData method without new value for status.
     *
     * @return void
     */
    public function testPrepareSystemLogDataWithoutNewValue()
    {
        $data = [
            'negotiated_price' => 3,
            'shipping' => 2,
            'subtotal' => 5,
        ];

        $this->assertEquals($data, $this->snapshotInformationManagement->prepareSystemLogData($data));
    }

    /**
     * Test getCustomerId method.
     *
     * @return void
     */
    public function testGetCustomerId()
    {
        $customerId = 1;
        $this->negotiableQuoteHelper->expects($this->once())->method('getSalesRepresentative')->willReturn($customerId);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertEquals($customerId, $this->snapshotInformationManagement->getCustomerId($quote));
    }
}
