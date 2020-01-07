<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

use Magento\NegotiableQuote\Model\History\SnapshotInformationManagement;

/**
 * Class SnapshotManagementTest
 */
class SnapshotManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\History\SnapshotManagement
     */
    private $snapshotManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\History\DiffProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $diffProcessor;

    /**
     * @var SnapshotInformationManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $snapshotInformationManagement;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->diffProcessor = $this->createMock(\Magento\NegotiableQuote\Model\History\DiffProcessor::class);
        $this->snapshotInformationManagement =
            $this->createMock(\Magento\NegotiableQuote\Model\History\SnapshotInformationManagement::class);

        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();

        $quoteExtension = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $negotiableQuote = $this->createMock(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class);
        $this->quoteRepository->expects($this->any())
            ->method('get')->withConsecutive([1], [2])->willReturnOnConsecutiveCalls($this->quote, null);
        $this->quote->expects($this->any())->method('getExtensionAttributes')->willReturn($quoteExtension);
        $quoteExtension->expects($this->any())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->any())->method('getStatus')->willReturn('quote_status');

        $this->snapshotInformationManagement->expects($this->any())
            ->method('collectCartData')->willReturn('cart_data');
        $this->snapshotInformationManagement->expects($this->any())
            ->method('collectCommentData')->willReturn(['comment_data']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->snapshotManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\History\SnapshotManagement::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'diffProcessor' => $this->diffProcessor,
                'snapshotInformationManagement' => $this->snapshotInformationManagement,
            ]
        );
    }

    /**
     * Test for collectSnapshotDataForNewQuote() method
     *
     * @return void
     */
    public function testCollectSnapshotDataForNewQuote()
    {
        $this->assertEquals(
            [
                'cart' => 'cart_data',
                'comments' => ['comment_data'],
                'status' => 'quote_status',
            ],
            $this->snapshotManagement->collectSnapshotDataForNewQuote(1)
        );
        $this->assertEquals([], $this->snapshotManagement->collectSnapshotDataForNewQuote(2));
    }

    /**
     * Test for collectSnapshotData() method
     *
     * @return void
     */
    public function testCollectSnapshotData()
    {
        $this->snapshotInformationManagement->expects($this->any())
            ->method('prepareSnapshotData')->willReturn(['snapshot_data']);

        $this->assertEquals(['snapshot_data'], $this->snapshotManagement->collectSnapshotData(1));
        $this->assertEquals([], $this->snapshotManagement->collectSnapshotData(2));
    }

    /**
     * Test for checkForSystemLogs() method
     *
     * @return void
     */
    public function testCheckForSystemLogs()
    {
        $data = [
            'status' => [
                'new_value' => \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_DECLINED,
            ],
            'subtotal' => 3,
        ];
        $this->assertEquals($data, $this->snapshotManagement->checkForSystemLogs($data + ['check_system' => true]));

        $this->snapshotInformationManagement->expects($this->any())
            ->method('prepareSystemLogData')->willReturn(['snapshot_data']);

        $this->assertEquals(['snapshot_data'], $this->snapshotManagement->checkForSystemLogs($data));
    }

    /**
     * Test for getCustomerId() method
     *
     * @param bool $isSeller
     * @param bool $isExpired
     * @param int $expectedResult
     * @return void
     * @dataProvider dataProviderGetCustomerId
     */
    public function testGetCustomerId($isSeller, $isExpired, $expectedResult)
    {
        $this->snapshotInformationManagement->expects($this->any())->method('getCustomerId')->willReturn(1);

        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->quote->expects($this->any())->method('getCustomer')->willReturn($customer);
        $customer->expects($this->any())->method('getId')->willReturn(2);

        $this->assertEquals(
            $expectedResult,
            $this->snapshotManagement->getCustomerId($this->quote, $isSeller, $isExpired)
        );
    }

    /**
     * Data provider getCustomerId
     *
     * @return array
     */
    public function dataProviderGetCustomerId()
    {
        return [
            [true, true, 0],
            [true, false, 1],
            [false, false, 2],
        ];
    }

    /**
     * Test for getQuote() method
     *
     * @param int $quoteId
     * @param \Magento\Quote\Api\Data\CartInterface|null $expectedResult
     * @return void
     * @dataProvider dataProviderGetQuote
     */
    public function testGetQuote($quoteId, $expectedResult)
    {
        $this->quoteRepository->expects($this->any())
            ->method('get')->with(3)->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $this->assertEquals($expectedResult, $this->snapshotManagement->getQuote($quoteId));
    }

    /**
     * Data provider getQuote
     *
     * @return array
     */
    public function dataProviderGetQuote()
    {
        return [
            [0, null],
            [1, $this->quote],
            [2, null],
            [3, null],
        ];
    }

    /**
     * Test for prepareCommentData() method
     *
     * @return void
     */
    public function testPrepareCommentData()
    {
        $this->assertEquals(
            [
                'comment' => 'comment_data',
                'status' => [
                    'new_value' => 'status_value',
                ]
            ],
            $this->snapshotManagement->prepareCommentData(1, ['status' => 'status_value'])
        );
    }

    /**
     * Test for getSnapshotsDiff() method
     *
     * @return void
     */
    public function testGetSnapshotsDiff()
    {
        $this->diffProcessor->expects($this->once())->method('processDiff')->willReturn(['snapshot_diff']);
        $this->assertEquals(
            ['snapshot_diff'],
            $this->snapshotManagement->getSnapshotsDiff(['old_snapshot'], ['new_snapshot'])
        );
    }
}
