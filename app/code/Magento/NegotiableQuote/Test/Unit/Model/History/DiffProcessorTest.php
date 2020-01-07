<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Test for class DiffProcessor.
 */
class DiffProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\History\DiffProcessor
     */
    private $diffProcessor;

    /**
     * @var \Magento\NegotiableQuote\Model\Expiration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expiration;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->expiration = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Expiration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->diffProcessor = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\History\DiffProcessor::class,
            ['expiration' => $this->expiration]
        );
    }

    /**
     * @return array
     */
    public function getCommentDataProvider()
    {
        return [
            [
                ['comments' => []],
                ['comments' => ['27']],
                ['comment' => '27']
            ],
            [
                ['comments' => ['12', '34', '56']],
                ['comments' => ['12', '34', '56']],
                []
            ],
            [
                ['comments' => ['12', '34', '56']],
                ['comments' => ['12', '34', '56', '78']],
                ['comment' => '78']
            ],
            [
                [],
                [],
                []
            ]
        ];
    }

    /**
     * @dataProvider getCommentDataProvider
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @param array $expectedResult
     */
    public function testProcessCommentDiff(array $oldSnapshot, array $currentSnapshot, array $expectedResult)
    {
        $result = $this->diffProcessor->processDiff($oldSnapshot, $currentSnapshot);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCartDataProvider()
    {
        return [
            [
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 5
                    ],
                    2 => [
                        'product_id' => 2,
                        'sku' => 'Test Product 2',
                        'qty' => 6
                    ],
                ]],
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 5
                    ],
                    2 => [
                        'product_id' => 2,
                        'sku' => 'Test Product 2',
                        'qty' => 6
                    ],
                ]],
                []
            ],
            [
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 5
                    ],
                    2 => [
                        'product_id' => 2,
                        'sku' => 'Test Product 2',
                        'qty' => 6
                    ],
                ]],
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 6
                    ],
                    2 => [
                        'product_id' => 2,
                        'sku' => 'Test Product 2',
                        'qty' => 5
                    ],
                ]],
                [
                    'updated_in_cart' => [
                        'Test Product' => [
                            'qty_changed' => [
                                'old_value' => 5,
                                'new_value' => 6
                            ],
                            'product_id' => 1
                        ],
                        'Test Product 2' => [
                            'qty_changed' => [
                                'old_value' => 6,
                                'new_value' => 5
                            ],
                            'product_id' => 2
                        ]
                    ]
                ]
            ],
            [
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 5
                    ],
                    2 => [
                        'product_id' => 2,
                        'sku' => 'Test Product 2',
                        'qty' => 6
                    ],
                ]],
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 5
                    ]
                ]],
                [
                    'removed_from_cart' => [
                        2 => [
                            'product_id' => 2,
                            'sku' => 'Test Product 2',
                            'qty' => 6
                        ]
                    ]
                ]
            ],
            [
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 5
                    ]
                ]],
                ['cart' => [
                    1 => [
                        'product_id' => 1,
                        'sku' => 'Test Product',
                        'qty' => 5
                    ],
                    2 => [
                        'options' => [
                            0 => [
                                'option' => 93,
                                'value' => '17',
                            ]
                        ],
                        'product_id' => 2,
                        'sku' => 'Test Product 2',
                        'qty' => 6
                    ]
                ]],
                [
                    'added_to_cart' => [
                        'Test Product 2' => [
                            'options' => [
                                0 => [
                                    'option' => 93,
                                    'value' => '17',
                                ]
                            ],
                            'qty' => 6,
                            'product_id' => 2
                        ]
                    ]
                ]
            ],
            [
                ['cart' => [
                    5 => [
                        'options' => [
                            0 => [
                                    'option' => 93,
                                    'value' => '16',
                            ],
                            1 => [
                                    'option' => 177,
                                    'value' => '15',
                            ],
                        ],
                        'product_id' => '12',
                        'sku' => 'Configurable-White-L',
                        'qty' => 1,
                    ],
                ]],
                ['cart' => [
                    5 => [
                        'options' => [
                            0 => [
                                'option' => 93,
                                'value' => '17',
                            ],
                            1 => [
                                'option' => 177,
                                'value' => '14',
                            ],
                        ],
                        'product_id' => '12',
                        'sku' => 'Configurable-White-L',
                        'qty' => 2,
                    ],
                ]],
                [
                    'updated_in_cart' => [
                        'Configurable-White-L' => [
                            'options_changed' => [
                                93 => [
                                    'old_value' => '16',
                                    'new_value' => '17',
                                ],
                                177 => [
                                    'old_value' => '15',
                                    'new_value' => '14',
                                ],
                            ],
                            'product_id' => '12',
                            'qty_changed' => [
                                'old_value' => 1,
                                'new_value' => 2,
                            ],
                        ],
                    ]
                ]
            ],
            [
                [],
                [],
                []
            ]
        ];
    }

    /**
     * @dataProvider getCartDataProvider
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @param array $expectedResult
     */
    public function testProcessCartDiff(array $oldSnapshot, array $currentSnapshot, array $expectedResult)
    {
        $result = $this->diffProcessor->processDiff($oldSnapshot, $currentSnapshot);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getPriceDataProvider()
    {
        return [
            [
                [],
                ['price' => ['type' => '1', 'value' => '27']],
                ['negotiated_price' => ['new_value' => ['1' => '27']]]
            ],
            [
                ['price' => ['type' => '1', 'value' => '27']],
                ['price' => ['type' => '3', 'value' => '12']],
                ['negotiated_price' => [
                    'old_value' => ['1' => '27'],
                    'new_value' => ['3' => '12']
                ]]
            ],
            [
                [],
                [],
                []
            ]
        ];
    }

    /**
     * @dataProvider getPriceDataProvider
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @param array $expectedResult
     */
    public function testProcessPriceDiff(array $oldSnapshot, array $currentSnapshot, array $expectedResult)
    {
        $result = $this->diffProcessor->processDiff($oldSnapshot, $currentSnapshot);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getShippingDataProvider()
    {
        return [
            [
                [],
                ['shipping' => ['method' => '1', 'price' => '27']],
                ['shipping' => ['new_value' => ['method' => '1', 'price' => '27']]]
            ],
            [
                ['shipping' => ['method' => '1', 'price' => '27']],
                ['shipping' => ['method' => '3', 'price' => '12']],
                ['shipping' => [
                    'old_value' => [
                        'method' => '1',
                        'price' => '27'
                    ],
                    'new_value' => [
                        'method' => '3',
                        'price' => '12'
                    ]
                ]]
            ],
            [
                [],
                [],
                []
            ]
        ];
    }

    /**
     * @dataProvider getShippingDataProvider
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @param array $expectedResult
     */
    public function testProcessShippingDiff(array $oldSnapshot, array $currentSnapshot, array $expectedResult)
    {
        $result = $this->diffProcessor->processDiff($oldSnapshot, $currentSnapshot);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getSimpleDataProvider()
    {
        return [
            [
                [],
                ['status' => NegotiableQuoteInterface::STATUS_CREATED],
                ['status' => ['new_value' => NegotiableQuoteInterface::STATUS_CREATED]]
            ],
            [
                ['status' => NegotiableQuoteInterface::STATUS_CREATED],
                ['status' => NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER],
                ['status' => [
                    'old_value' => NegotiableQuoteInterface::STATUS_CREATED,
                    'new_value' => NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER
                ]]
            ],
            [
                [],
                [],
                []
            ]
        ];
    }

    /**
     * Data provider for check expiration date diff.
     *
     * @return array
     */
    public function expirationDataProvider()
    {

        return [
            [
                [],
                ['expiration_date' => '12-04-2016'],
                ['expiration_date' => ['new_value' => '12-04-2016']],
                1,
                '11-04-2016'
            ],
            [
                [],
                ['expiration_date' => '12-04-2016'],
                [],
                1,
                '12-04-2016'
            ],
            [
                ['expiration_date' => '01-04-2016'],
                ['expiration_date' => '12-04-2016'],
                ['expiration_date' => [
                    'old_value' => '01-04-2016',
                    'new_value' => '12-04-2016'
                ]],
                0,
                ''
            ],
            [
                [],
                [],
                [],
                0,
                ''
            ]
        ];
    }

    /**
     * @dataProvider getSimpleDataProvider
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @param array $expectedResult
     */
    public function testProcessSimpleDiff(array $oldSnapshot, array $currentSnapshot, array $expectedResult)
    {
        $result = $this->diffProcessor->processDiff($oldSnapshot, $currentSnapshot);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test method processDiff for expiration date.
     *
     * @dataProvider expirationDataProvider
     * @param array $oldSnapshot
     * @param array $currentSnapshot
     * @param array $expectedResult
     * @param int $getDateCount
     * @param string $dateDefault
     * @return void
     */
    public function testProcessExpirationDateDiff(
        array $oldSnapshot,
        array $currentSnapshot,
        array $expectedResult,
        $getDateCount,
        $dateDefault
    ) {
        $date = $this->getMockBuilder(\DateTime::class)->disableOriginalConstructor()->getMock();
        $this->expiration->expects($this->exactly($getDateCount))
            ->method('retrieveDefaultExpirationDate')->willReturn($date);
        $date->expects($this->exactly($getDateCount))->method('format')->willReturn($dateDefault);

        $result = $this->diffProcessor->processDiff($oldSnapshot, $currentSnapshot);
        $this->assertEquals($expectedResult, $result);
    }
}
