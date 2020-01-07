<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote;

use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Unit test for Magento\NegotiableQuote\Block\Quote\History class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layout;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogCommentsInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyLogCommentsInformation;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyLogInformation;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogProductInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyLogProductInformation;

    /**
     * @var \Magento\NegotiableQuote\Block\Quote\History
     */
    private $history;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->negotiableQuoteHelper = $this->getMockBuilder(
            \Magento\NegotiableQuote\Helper\Quote::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->historyLogCommentsInformation = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\History\LogCommentsInformation::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyLogInformation = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\History\LogInformation::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyLogProductInformation = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\History\LogProductInformation::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->history = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\History::class,
            [
                '_urlBuilder' => $this->urlBuilder,
                '_escaper' => $this->escaper,
                '_layout' => $this->layout,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'historyLogCommentsInformation' => $this->historyLogCommentsInformation,
                'historyLogInformation' => $this->historyLogInformation,
                'historyLogProductInformation' => $this->historyLogProductInformation,
            ]
        );
    }

    /**
     * Test getLogAuthor method.
     *
     * @return void
     */
    public function testGetLogAuthor()
    {
        $quoteId = 245;
        $historyLog = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\HistoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteHelper->expects($this->once())
            ->method('resolveCurrentQuote')
            ->willReturn($quote);
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->historyLogCommentsInformation->expects($this->once())
            ->method('getLogAuthor')
            ->with($historyLog, $quoteId)
            ->willReturn('Log Author');
        $this->assertEquals('Log Author', $this->history->getLogAuthor($historyLog));
    }

    /**
     * Test getQuoteHistory method.
     *
     * @return void
     */
    public function testGetQuoteHistory()
    {
        $quoteHistoryCollectionMock = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\History\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyLogInformation->expects($this->once())
            ->method('getQuoteHistory')
            ->willReturn($quoteHistoryCollectionMock);

        $this->assertSame(
            $quoteHistoryCollectionMock,
            $this->history->getQuoteHistory()
        );
    }

    /**
     * Test getLogStatusMessage method.
     *
     * @param string $status
     * @param string $expectedMessage
     * @return void
     * @dataProvider getLogStatusMessageDataProvider
     */
    public function testGetLogStatusMessage($status, $expectedMessage)
    {
        $historyMock = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\HistoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $historyMock->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($status);

        $this->assertEquals(
            (string)$this->history->getLogStatusMessage($historyMock),
            $expectedMessage
        );
    }

    /**
     * Data provider for getLogStatusMessage method.
     *
     * @return array
     */
    public function getLogStatusMessageDataProvider()
    {
        return [
            [HistoryInterface::STATUS_CREATED, 'created quote'],
            [HistoryInterface::STATUS_CLOSED, 'closed quote'],
            [HistoryInterface::STATUS_UPDATED, 'updated quote'],
            [false, ''],
        ];
    }

    /**
     * Test getPriceValue method.
     *
     * @param array $price
     * @param string $expectedLabel
     * @return void
     * @dataProvider getPriceValueDataProvider
     */
    public function testGetPriceValue(array $price, $expectedLabel)
    {
        $this->negotiableQuoteHelper->expects($this->atLeastOnce())->method('formatPrice')->willReturnArgument(0);

        $this->assertEquals($expectedLabel, (string)$this->history->getPriceValue($price));
    }

    /**
     * Data provider for getPriceValue method.
     *
     * @return array
     */
    public function getPriceValueDataProvider()
    {
        return [
            [
                [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT => 10],
                'Percentage Discount - 10%'
            ],
            [
                [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT => 15],
                'Amount Discount - 15'
            ],
            [
                [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL => 20],
                'Proposed Price - 20'
            ],
            [
                [777 => 25],
                ''
            ]
        ];
    }

    /**
     * Test getRemovedPriceValues method.
     *
     * @param array $price
     * @param array $priceData
     * @return void
     * @dataProvider getRemovedPriceValuesDataProvider
     */
    public function testGetRemovedPriceValues(array $price, array $priceData)
    {
        $this->negotiableQuoteHelper->expects($this->atLeastOnce())->method('formatPrice')->willReturnArgument(0);

        $this->assertEquals($priceData, $this->history->getRemovedPriceValues($price));
    }

    /**
     * Data provider for getPriceValue method.
     *
     * @return array
     */
    public function getRemovedPriceValuesDataProvider()
    {
        return [
            [
                [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT => 10],
                ['method' => 'Percentage Discount', 'value' => '10%']
            ],
            [
                [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT => 15],
                ['method' => 'Amount Discount', 'value' => '15']
            ],
            [
                [NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL => 20],
                ['method' => 'Proposed Price', 'value' => '20']
            ],
            [
                [777 => 25],
                []
            ]
        ];
    }

    /**
     * Test getUpdates method.
     *
     * @param array $updates
     * @return void
     * @dataProvider getUpdatesDataProvider
     */
    public function testGetUpdates(array $updates)
    {
        $logId = 23;
        $this->historyLogInformation->expects($this->once())
            ->method('getQuoteUpdates')
            ->with($logId)
            ->willReturn($updates);

        $this->assertEquals($updates, $this->history->getUpdates($logId));
    }

    /**
     * Data provider for getUpdates method.
     *
     * @return array
     */
    public function getUpdatesDataProvider()
    {
        return [
            [
                [
                    'shipping' => 1,
                    'test' => null
                ]
            ]
        ];
    }

    /**
     * Test getCommentText method.
     *
     * @param string $comment
     * @param string $expectedResult
     * @return void
     * @dataProvider getCommentTextDataProvider
     */
    public function testGetCommentText($comment, $expectedResult)
    {
        $commentId = 3;
        $this->historyLogCommentsInformation->expects($this->once())
            ->method('getCommentText')
            ->with($commentId)
            ->willReturn($comment);

        $this->assertEquals(
            $expectedResult,
            $this->history->getCommentText($commentId)
        );
    }

    /**
     * Data provider for getCommentText method.
     *
     * @return array
     */
    public function getCommentTextDataProvider()
    {
        return [
            [
                'Comment 1',
                'Comment 1'
            ],
            [
                null,
                ''
            ]
        ];
    }

    /**
     * Test getCommentAttachments method.
     *
     * @return void
     */
    public function testGetCommentAttachments()
    {
        $commentId = 3;
        $commentsCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyLogCommentsInformation->expects($this->once())
            ->method('getCommentAttachments')
            ->with($commentId)
            ->willReturn($commentsCollection);

        $this->assertEquals(
            $commentsCollection,
            $this->history->getCommentAttachments($commentId)
        );
    }

    /**
     * Test getAttachmentUrl method.
     *
     * @return void
     */
    public function testGetAttachmentUrl()
    {
        $attachmentUrl = 'attachment_url';
        $this->urlBuilder->expects($this->once())->method('getUrl')->willReturn($attachmentUrl);

        $this->assertEquals($attachmentUrl, $this->history->getAttachmentUrl(1));
    }

    /**
     * Test getStatusLabel method.
     *
     * @return void
     */
    public function testGetStatusLabel()
    {
        $status = 'created';
        $statusLabel = 'Open';
        $this->historyLogCommentsInformation->expects($this->once())
            ->method('getStatusLabel')
            ->with($status)
            ->willReturn($statusLabel);

        $this->assertEquals($statusLabel, $this->history->getStatusLabel($status));
    }

    /**
     * Test formatPrice method.
     *
     * @param string $priceString
     * @param string $formattedPrice
     * @return void
     * @dataProvider formatPriceDataProvider
     */
    public function testFormatPrice($priceString, $formattedPrice)
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteHelper->expects($this->once())
            ->method('resolveCurrentQuote')
            ->willReturn($quote);
        $quote->expects($this->once())->method('getCurrency')->willReturn($currency);
        $currency->expects($this->once())->method('getBaseCurrencyCode')->willReturn('USD');
        $this->negotiableQuoteHelper->expects($this->once())->method('formatPrice')->willReturn($formattedPrice);

        $this->assertEquals($formattedPrice, $this->history->formatPrice($priceString));
    }

    /**
     * Data provider for formatPrice method.
     *
     * @return array
     */
    public function formatPriceDataProvider()
    {
        return [
            ['1.25', '1.25'],
            ['1.2', '1.20'],
            ['1', '1.00']
        ];
    }

    /**
     * Test getProductName method.
     *
     * @return void
     */
    public function testGetProductName()
    {
        $sku = 'test_sku';
        $productName = 'Product name';
        $this->historyLogProductInformation->expects($this->once())
            ->method('getProductName')
            ->with($sku)
            ->willReturn($productName);

        $this->assertEquals($productName, $this->history->getProductName($sku));
    }

    /**
     * Test getProductNameById method.
     *
     * @return void
     */
    public function testGetProductNameById()
    {
        $productName = 'Product name';
        $productId = 2;
        $this->historyLogProductInformation->expects($this->once())
            ->method('getProductNameById')
            ->with($productId)
            ->willReturn($productName);

        $this->assertEquals($productName, $this->history->getProductNameById($productId));
    }

    /**
     * Test getAddressHtml method.
     *
     * @param array $addressArray
     * @param bool $isSetPostcode
     * @param string|null $addressHtml
     * @param \Magento\Framework\Phrase $expectedResult
     * @return void
     * @dataProvider getAddressHtmlDataProvider
     */
    public function testGetAddressHtml(
        array $addressArray,
        $isSetPostcode,
        $addressHtml,
        \Magento\Framework\Phrase $expectedResult
    ) {
        $renderer = $this->getMockBuilder(\Magento\Customer\Block\Address\Renderer\RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->historyLogInformation->expects($this->once())->method('isSetPostcode')->willReturn($isSetPostcode);
        if ($isSetPostcode) {
            $this->historyLogInformation->expects($this->once())
                ->method('getLogAddressRenderer')
                ->willReturn($renderer);
            $renderer->expects($this->once())->method('renderArray')->with($addressArray)->willReturn($addressHtml);
        }

        $this->assertEquals($expectedResult, $this->history->getAddressHtml($addressArray));
    }

    /**
     * Data provider for getAddressHtml method.
     *
     * @return array
     */
    public function getAddressHtmlDataProvider()
    {
        return [
            [
                ['street' => 'street 1', 'postcode' => '12345'],
                true,
                'Street: street1, Postcode: 12345',
                __('Street: street1, Postcode: 12345')
            ],
            [['street' => 'street 1'], false, null, __('None')]
        ];
    }

    /**
     * Test getShippingMethodName method.
     *
     * @param array $data
     * @param string $methodName
     * @return void
     * @dataProvider getShippingMethodNameDataProvider
     */
    public function testGetShippingMethodName(array $data, $methodName)
    {
        $this->assertEquals($methodName, $this->history->getShippingMethodName($data));
    }

    /**
     * Data provider for getShippingMethodName method.
     *
     * @return array
     */
    public function getShippingMethodNameDataProvider()
    {
        return [
            [['method' => 'Method name'], 'Method name'],
            [[], 'None']
        ];
    }

    /**
     * Test formatExpirationDate method.
     *
     * @param int $dateType
     * @return void
     * @dataProvider formatExpirationDateDataProvider
     */
    public function testFormatExpirationDate($dateType)
    {
        $date = date('Y-m-d H:i:s');
        $dateObject = new \DateTime($date);
        $formatter = new \IntlDateFormatter(
            $this->localeResolver->getLocale(),
            $dateType,
            \IntlDateFormatter::NONE,
            null,
            null,
            null
        );
        $formattedDate = $formatter->format($dateObject);
        $this->historyLogInformation->expects($this->once())
            ->method('formatDate')
            ->with($date, $dateType)
            ->willReturn($formattedDate);

        $this->assertEquals($formattedDate, $this->history->formatExpirationDate($date, $dateType));
    }

    /**
     * Data provider for formatExpirationDate method.
     *
     * @return array
     */
    public function formatExpirationDateDataProvider()
    {
        return [
            [\IntlDateFormatter::LONG],
            [\IntlDateFormatter::FULL],
            [\IntlDateFormatter::MEDIUM],
            [\IntlDateFormatter::SHORT]
        ];
    }

    /**
     * Test checkMultiStatus.
     *
     * @param string $oldValue
     * @param string $newValue
     * @param array $multiStatuses
     * @return void
     * @dataProvider checkMultiStatusDataProvider
     */
    public function testCheckMultiStatus($oldValue, $newValue, array $multiStatuses)
    {
        $this->assertEquals($multiStatuses, $this->history->checkMultiStatus($oldValue, $newValue));
    }

    /**
     * Data provider for checkMultiStatus method.
     *
     * @return array
     */
    public function checkMultiStatusDataProvider()
    {
        return [
            [
                NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
                NegotiableQuoteInterface::STATUS_ORDERED,
                [
                    [
                        'old_value' => NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
                        'new_value' => NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER
                    ],
                    [
                        'old_value' => NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
                        'new_value' => NegotiableQuoteInterface::STATUS_ORDERED
                    ]
                ]
            ],
            [
                NegotiableQuoteInterface::STATUS_DECLINED,
                NegotiableQuoteInterface::STATUS_ORDERED,
                [
                    [
                        'old_value' => NegotiableQuoteInterface::STATUS_DECLINED,
                        'new_value' => NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER
                    ],
                    [
                        'old_value' => NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
                        'new_value' => NegotiableQuoteInterface::STATUS_ORDERED
                    ]
                ]
            ],
            [
                NegotiableQuoteInterface::STATUS_ORDERED,
                NegotiableQuoteInterface::STATUS_ORDERED,
                []
            ]
        ];
    }

    /**
     * Test isCanSubmit.
     *
     * @param bool $isCanSubmit
     * @return void
     * @dataProvider isCanSubmitDataProvider
     */
    public function testIsCanSubmit($isCanSubmit)
    {
        $this->historyLogInformation->expects($this->once())->method('isCanSubmit')->willReturn($isCanSubmit);

        $this->assertEquals($isCanSubmit, $this->history->isCanSubmit());
    }

    /**
     * DataProvider for isCanSubmit method.
     *
     * @return array isCanSubmit
     */
    public function isCanSubmitDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Test getProductAddStringHtml method.
     *
     * @param array $productData
     * @param array $configurableAttributes
     * @param string $productAddString
     * @return void
     * @dataProvider getProductAddStringHtmlDataProvider
     */
    public function testGetProductAddStringHtml(array $productData, array $configurableAttributes, $productAddString)
    {
        $this->historyLogProductInformation->expects($this->once())
            ->method('getProductAttributes')
            ->with($productData['product_id'])
            ->willReturn($configurableAttributes);
        $this->escaper->expects($this->atLeastOnce())->method('escapeHtml')->willReturnArgument(0);

        $this->assertEquals($productAddString, $this->history->getProductAddStringHtml($productData));
    }

    /**
     * DataProvider getProductAddStringHtml.
     *
     * @return array
     */
    public function getProductAddStringHtmlDataProvider()
    {
        return [
            [
                [
                    'product_id' => '1',
                    'qty' => '5',
                    'options' => [
                        [
                            'option' => 'option',
                            'value' => 'value'
                        ],
                    ]
                ],
                [
                    'option' => [
                        'label' => 'label',
                        'values' => [
                            [
                                'label' => 'value',
                                'value_index' => 'value'
                            ]
                        ]
                    ]
                ],
                'Qty: 5, label: value'
            ],
            [
                [
                    'product_id' => '1',
                    'qty' => '5',
                    'options' => [
                        [
                            'option' => 'option',
                            'value' => 'value'
                        ],
                    ]
                ],
                [
                    'option' => [
                        'values' => [
                            [
                                'label' => 'value',
                                'value_index' => 'value'
                            ]
                        ]
                    ]
                ],
                'Qty: 5, deleted: value'
            ],
            [
                [
                    'product_id' => '1',
                    'qty' => '5',
                    'options' => [
                        [
                            'option' => 'option',
                            'value' => 'value'
                        ],
                    ]
                ],
                [
                    'option' => [
                        'label' => 'label'
                    ]
                ],
                'Qty: 5, label: deleted'
            ],
            [
                [
                    'product_id' => '1',
                    'qty' => '5',
                    'options' => [
                        [
                            'option' => 'option',
                            'value' => 'value'
                        ],
                    ]
                ],
                [],
                'Qty: 5, deleted: deleted'
            ],
            [
                [
                    'product_id' => '1',
                    'qty' => '5',
                    'options' => [
                        ['option' => 'option', 'value' => 'valueText'],
                    ]
                ],
                [
                    'option' => [ 'label' => 'label', 'values' => []]
                ],
                'Qty: 5, label: valueText'
            ],
        ];
    }

    /**
     * Test getProductUpdateStringHtml.
     *
     * @param array $productUpdates
     * @param array $configurableAttributes
     * @param string $qtyHtml
     * @param string $optionsHtml
     * @param string $updateString
     * @return void
     * @dataProvider getProductUpdateStringHtmlDataProvider
     */
    public function testGetProductUpdateStringHtml(
        array $productUpdates,
        array $configurableAttributes,
        $qtyHtml,
        $optionsHtml,
        $updateString
    ) {
        $this->escaper->expects($this->atLeastOnce())->method('escapeHtml')->willReturnArgument(0);
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $block->expects($this->atLeastOnce())->method('setData')->willReturnSelf();
        $block->expects($this->atLeastOnce())->method('toHtml')
            ->willReturnOnConsecutiveCalls($qtyHtml, $optionsHtml, $qtyHtml, $optionsHtml, $qtyHtml);
        $this->layout->expects($this->atLeastOnce())->method('getBlock')->willReturn($block);
        if (isset($productUpdates['options_changed'])) {
            $this->historyLogProductInformation->expects($this->once())
                ->method('getProductAttributes')
                ->with($productUpdates['product_id'])
                ->willReturn($configurableAttributes);
        }

        $this->assertEquals($updateString, $this->history->getProductUpdateStringHtml($productUpdates));
    }

    /**
     * Data provider for getProductUpdateStringHtml method.
     *
     * @return array
     */
    public function getProductUpdateStringHtmlDataProvider()
    {
        return [
            [
                [
                    'product_id' => 1,
                    'qty_changed' => true,
                    'old_value' => 1,
                    'new_value' => 3,
                    'options_changed' => [
                        'option' => [
                            'option' => 'option',
                            'old_value' => 'old_value',
                            'new_value' => 'new_value'
                        ]
                    ]
                ],
                [
                    'option' => [
                        'label' => 'label',
                        'values' => [
                            [
                                'label' => 'old value',
                                'value_index' => 'old_value'
                            ],
                            [
                                'label' => 'new value',
                                'value_index' => 'new_value'
                            ]
                        ]
                    ]
                ],
                '1 - 3',
                'old value - new value',
                'Qty: 1 - 3, label: old value - new value'
            ],
            [
                [
                    'product_id' => 1,
                    'qty_changed' => true,
                    'old_value' => 1,
                    'new_value' => 3,
                    'options_changed' => [
                        'option' => [
                            'option' => 'option',
                            'old_value' => 'old_value',
                            'new_value' => 'new_value'
                        ]
                    ]
                ],
                [
                    'option' => [
                        'values' => [
                            [
                                'label' => 'old value',
                                'value_index' => 'old_value'
                            ],
                            [
                                'label' => 'new value',
                                'value_index' => 'new_value'
                            ]
                        ]
                    ]
                ],
                '1 - 3',
                'old value - new value',
                'Qty: 1 - 3, deleted: old value - new value'
            ],
            [
                [
                    'product_id' => 1,
                    'qty_changed' => true,
                    'old_value' => 1,
                    'new_value' => 3
                ],
                [
                    'option' => [
                        'values' => [
                            [
                                'label' => 'old value',
                                'value_index' => 'old_value'
                            ],
                            [
                                'label' => 'new value',
                                'value_index' => 'new_value'
                            ]
                        ]
                    ]
                ],
                '1 - 3',
                '',
                'Qty: 1 - 3'
            ]
        ];
    }
}
