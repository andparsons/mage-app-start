<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\ResourceModel\History\CollectionFactory;

/**
 * Class HistoryManagementTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HistoryManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\History\SnapshotManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $snapshotManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\History\CriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagement
     */
    private $historyManagement;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var int|null
     */
    private $quoteId;

    /**
     * @var \Magento\NegotiableQuote\Model\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyItem;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResults;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteria;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Set up.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->historyRepository = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\HistoryRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->historyFactory = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->snapshotManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\History\SnapshotManagement::class)
            ->disableOriginalConstructor()->getMock();
        $this->criteriaBuilder = $this->getMockBuilder(\Magento\NegotiableQuote\Model\History\CriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->historyManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\HistoryManagement::class,
            [
                'historyRepository' => $this->historyRepository,
                'historyFactory' => $this->historyFactory,
                'snapshotManagement' => $this->snapshotManagement,
                'criteriaBuilder' => $this->criteriaBuilder,
                'serializer' => $this->serializerMock
            ]
        );

        $this->quoteId = 1;
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->quote->expects($this->any())->method('getId')->willReturn($this->quoteId);
        $this->snapshotManagement->expects($this->any())->method('getQuote')->with(1)->willReturn($this->quote);
        $quoteExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->quote->expects($this->any())->method('getExtensionAttributes')->willReturn($quoteExtension);
        $quoteExtension->expects($this->any())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->any())->method('getStatus')->willReturn('quote_status');
        $historyLog = $this->getMockBuilder(\Magento\NegotiableQuote\Model\History::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $this->historyFactory->expects($this->any())->method('create')->willReturn($historyLog);
        $this->searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->criteriaBuilder->expects($this->any())->method('getQuoteHistoryCriteria')
            ->willReturn($this->searchCriteria);
        $this->criteriaBuilder->expects($this->any())
            ->method('getQuoteSearchCriteria')
            ->willReturn($this->searchCriteria);
        $this->criteriaBuilder->expects($this->any())
            ->method('getSystemHistoryCriteria')
            ->willReturn($this->searchCriteria);
        $this->historyItem = $this->getMockBuilder(\Magento\NegotiableQuote\Model\History::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLogData', 'getSnapshotData', 'getStatus', 'setIsDraft', 'setStatus', 'setLogData'])
            ->getMock();
        $this->searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->searchResults->expects($this->any())->method('getItems')->willReturn([$this->historyItem]);
        $this->historyRepository->expects($this->any())->method('getList')->willReturn($this->searchResults);
    }

    /**
     * Test for createLog() method.
     *
     * @return void
     */
    public function testCreateLog()
    {
        $this->snapshotManagement->expects($this->any())
            ->method('collectSnapshotDataForNewQuote')->willReturn(['snapshot_data']);
        $this->snapshotManagement->expects($this->any())
            ->method('prepareCommentData')->willReturn(['comment_data', 'system_data' => true]);
        $this->snapshotManagement->expects($this->any())
            ->method('checkForSystemLogs')->willReturn(['system_data' => []]);
        $this->snapshotManagement->expects($this->any())
            ->method('getQuoteForRemovedItem')
            ->with($this->searchCriteria)
            ->willReturn($this->quote);

        $this->historyManagement->createLog($this->quoteId);
    }

    /**
     * Test for createLog() method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateLogWithException()
    {
        $this->snapshotManagement->expects($this->any())
            ->method('collectSnapshotDataForNewQuote')->willReturn(['snapshot_data']);
        $this->snapshotManagement->expects($this->any())
            ->method('prepareCommentData')->willReturn(['comment_data', 'system_data' => true]);
        $this->snapshotManagement->expects($this->any())
            ->method('checkForSystemLogs')->willReturn(['system_data' => []]);
        $this->snapshotManagement->expects($this->any())
            ->method('getQuoteForRemovedItem')
            ->with($this->searchCriteria)
            ->willReturn($this->quote);

        $this->historyRepository->expects($this->any())->method('save')->willThrowException(
            new \Exception()
        );
        $this->historyManagement->createLog($this->quoteId);
    }

    /**
     * Test for updateLog() method.
     *
     * @return void
     */
    public function testUpdateLog()
    {
        $this->snapshotManagement->expects($this->once())
            ->method('collectSnapshotData')->willReturn(['snapshot_data']);
        $this->snapshotManagement->expects($this->once())
            ->method('getSnapshotsDiff')->willReturn(['snapshot_diff']);

        $this->historyManagement->updateLog($this->quoteId);
        $this->historyManagement->updateLog(null);
    }

    /**
     * Test for closeLog() method.
     *
     * @return void
     */
    public function testCloseLog()
    {
        $this->historyManagement->closeLog($this->quoteId);
    }

    /**
     * Test for updateStatusLog() method.
     *
     * @param string $snapshotData
     * @return void
     * @dataProvider dataProviderUpdateStatusLog
     */
    public function testUpdateStatusLog($snapshotData)
    {
        $this->historyItem->expects($this->any())
            ->method('getSnapshotData')->willReturn($snapshotData);

        $this->historyManagement->updateStatusLog($this->quoteId);
    }

    /**
     * Test for addCustomLog() method.
     *
     * @param string $logStatus
     * @param array $logData
     * @param array $newData
     * @param array $expectLogResult
     * @return void
     * @dataProvider dataProviderAddCustomLog
     */
    public function testAddCustomLog($logStatus, array $logData, array $newData, array $expectLogResult)
    {
        $this->historyItem->expects($this->any())->method('getStatus')->willReturn($logStatus);
        $this->historyItem->expects($this->any())->method('getLogData')->willReturn(json_encode($logData));
        $this->historyItem->expects($this->any())->method('getSnapshotData')->willReturn(json_encode($logData));
        $this->historyItem->expects($this->once())->method('setLogData')->with(json_encode($expectLogResult));

        $this->historyRepository->expects($this->once())->method('save')->with($this->historyItem);

        $this->historyManagement->addCustomLog($this->quoteId, $newData);
    }

    /**
     * Test getLogUpdatesList method.
     *
     * @return void
     */
    public function testGetLogUpdatesList()
    {
        $logId = 1;
        $history = $this->getMockBuilder(\Magento\NegotiableQuote\Model\History::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLogData'])
            ->getMock();
        $logData = json_encode([0 => 'test']);

        $history->expects($this->atLeastOnce())->method('getLogData')->willReturn($logData);
        $this->historyRepository->expects($this->atLeastOnce())->method('get')->willReturn($history);
        $this->assertEquals(['test'], $this->historyManagement->getLogUpdatesList($logId));
    }

    /**
     * Test getLogUpdatesList method with empty result.
     *
     * @return void
     */
    public function testGetLogUpdatesListWithEmptyResult()
    {
        $this->assertEquals([], $this->historyManagement->getLogUpdatesList(null));
    }

    /**
     * Test for method updateDraftLogs.
     *
     * @return void
     */
    public function testUpdateDraftLogs()
    {
        $this->historyItem->expects($this->any())->method('setIsDraft')->will($this->returnSelf());
        $this->historyRepository->expects($this->any())
            ->method('save')->with($this->historyItem)->willReturn($this->historyItem);
        $this->historyManagement->updateDraftLogs($this->quoteId);
        $this->historyManagement->updateDraftLogs($this->quoteId, true);
    }

    /**
     * Test for method updateSystemLogsStatus.
     *
     * @return void
     */
    public function testUpdateSystemLogsStatus()
    {
        $this->searchResults->expects($this->once())->method('getTotalCount')->willReturn(1);
        $this->historyManagement->updateSystemLogsStatus($this->quoteId);
    }

    /**
     * Data provider updateStatusLog.
     *
     * @return array
     */
    public function dataProviderUpdateStatusLog()
    {
        return [
            [json_encode(['status' => 'updated_by_system'])],
            ['{}'],
            [null],
        ];
    }

    /**
     * Data provider addCustomLog.
     *
     * @return array
     */
    public function dataProviderAddCustomLog()
    {
        return [
            [
                \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                [
                    'status' => \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                    'custom_log' => [
                        [
                            'product_sku' => 'sample_sku',
                            'values' => [
                                'key1' => 'value1',
                            ],
                        ],
                        [
                            'product_sku' => 'sample_sku',
                            'values' => [
                                'key2' => 'value2',
                            ],
                        ],
                    ],
                ],
                ['sample_value'],
                [
                    'custom_log' => [
                        [
                            'product_sku' => 'sample_sku',
                            'values' => [
                                'key1' => 'value1',
                            ],
                        ],
                        [
                            'product_sku' => 'sample_sku',
                            'values' => [
                                'key2' => 'value2',
                            ],
                        ],
                        'sample_value',
                    ],
                    'status' => \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                ]
            ],
            [
                \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                ['status' => \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM],
                [],
                ['status' => \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM]
            ],
            [
                \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                [
                    'status' => \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                    'custom_log' => [
                        [
                            'product_sku' => 'sample_sku',
                            'field_id' => 'product_sku',
                            'values' => [
                                [
                                    'field_id' => 'cart_price',
                                    'old_value' => 100,
                                    'new_value' => 90
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'product_sku' => 'sample_sku',
                        'field_id' => 'product_sku',
                        'values' => [
                            [
                                'field_id' => 'cart_price',
                                'old_value' => 90,
                                'new_value' => 85
                            ],
                        ],
                    ],
                ],
                [
                    'custom_log' => [
                        [
                            'product_sku' => 'sample_sku',
                            'field_id' => 'product_sku',
                            'values' => [
                                [
                                    'field_id' => 'cart_price',
                                    'old_value' => 100,
                                    'new_value' => 85
                                ],
                            ],
                        ],
                    ],
                    'status' => \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                ]
            ],
        ];
    }

    /**
     * Test for addCustomLog() method with exeption.
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddCustomLogWithExeption()
    {
        $logData = [
            'status' => \Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
            'custom_log' => [
                [
                    'product_sku' => 'sample_sku',
                    'values' => [
                        'key1' => 'value1',
                    ],
                ],
            ],
        ];
        $this->historyItem->expects($this->any())->method('getStatus')
            ->willReturn(\Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_UPDATED_BY_SYSTEM);
        $this->historyItem->expects($this->any())->method('getLogData')->willReturn(json_encode($logData));
        $this->historyItem->expects($this->any())->method('getSnapshotData')->willReturn(json_encode($logData));

        $this->historyRepository->expects($this->once())->method('save')
            ->with($this->historyItem)->willThrowException(new \Exception());

        $this->historyManagement->addCustomLog($this->quoteId, ['sample_value']);
    }
}
