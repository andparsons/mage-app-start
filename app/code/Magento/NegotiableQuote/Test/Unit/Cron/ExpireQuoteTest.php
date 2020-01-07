<?php

namespace Magento\NegotiableQuote\Test\Unit\Cron;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid;

/**
 * Unit test for ExpireQuote.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpireQuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\Expiration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expiration;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Expired\Provider\ExpiredQuoteList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expiredQuoteList;

    /**
     * @var \Magento\NegotiableQuote\Model\Expired\MerchantNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $merchantNotifier;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\NegotiableQuote\Cron\ExpireQuote
     */
    private $expiredQuote;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->negotiableQuoteRepository = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList'])
            ->getMockForAbstractClass();
        $this->quote = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStatus',
                'setExpirationPeriod',
                'setQuoteId',
                'getQuoteId',
                'getId',
                'setSnapshot',
                'getSnapshot',
                'setStatus'
            ])
            ->getMockForAbstractClass();
        $this->quoteGrid = $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->expiration = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Expiration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\HistoryManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'createLog',
                    'updateLog',
                    'closeLog',
                    'updateStatusLog',
                    'getQuoteHistory',
                    'getLogUpdatesList'
                ]
            )
            ->getMockForAbstractClass();
        $this->expiredQuoteList = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Expired\Provider\ExpiredQuoteList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->merchantNotifier = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Expired\MerchantNotifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->expiredQuote = $objectManager->getObject(
            \Magento\NegotiableQuote\Cron\ExpireQuote::class,
            [
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'quoteGrid' => $this->quoteGrid,
                'expiration' => $this->expiration,
                'historyManagement' => $this->historyManagement,
                'expiredQuoteList' => $this->expiredQuoteList,
                'merchantNotifier' => $this->merchantNotifier,
                'logger' => $this->logger,
                'serializer' => $this->serializer,
            ]
        );
    }

    /**
     * Test for method execute().
     *
     * @return void
     */
    public function testExecute()
    {
        $quoteId = 1;
        $extensionAttributes = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->any())
            ->method('getNegotiableQuote')
            ->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
        $this->quote->expects($this->once())
            ->method('setExpirationPeriod')
            ->willReturnSelf();
        $this->expiration->expects($this->once())
            ->method('retrieveDefaultExpirationDate')
            ->willReturn(new \DateTime);
        $this->quote->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId)
            ->willReturnSelf();
        $this->quote->expects($this->once())->method('getQuoteId')->willReturn(1);
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('save')
            ->with($this->quote)
            ->willReturn(true);
        $this->quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->expiredQuoteList->expects($this->once())->method('getExpiredQuotes')->willReturn([$this->quote]);
        $this->quoteGrid->expects($this->once())->method('refreshValue')->with(
            QuoteGrid::QUOTE_ID,
            $quoteId,
            QuoteGrid::QUOTE_STATUS,
            NegotiableQuoteInterface::STATUS_EXPIRED
        )->willReturnSelf();
        $this->historyManagement->expects($this->once())
            ->method('updateStatusLog')
            ->with($quoteId, false, true);

        $this->expiredQuote->execute();
    }

    /**
     * Test for method execute() with change status quote.
     *
     * @return void
     */
    public function testExecuteChangeStatus()
    {
        $quoteId = 1;
        $extensionAttributes = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->any())
            ->method('getNegotiableQuote')
            ->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER);
        $this->quote->expects($this->once())
            ->method('setStatus')
            ->willReturnSelf();
        $this->quote->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId)
            ->willReturnSelf();
        $this->quote->expects($this->once())->method('getQuoteId')->willReturn(1);
        $snapshotArray = [
            'negotiable_quote' =>
                [
                    NegotiableQuoteInterface::QUOTE_STATUS => NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER
                ]
        ];
        $this->serializer->expects($this->atLeastOnce())->method('unserialize')
            ->willReturn($snapshotArray);

        $this->negotiableQuoteRepository->expects($this->once())
            ->method('save')
            ->with($this->quote)
            ->willReturn(true);
        $this->quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->expiredQuoteList->expects($this->once())->method('getExpiredQuotes')->willReturn([$this->quote]);
        $this->quoteGrid->expects($this->once())->method('refreshValue')->with(
            QuoteGrid::QUOTE_ID,
            $quoteId,
            QuoteGrid::QUOTE_STATUS,
            NegotiableQuoteInterface::STATUS_EXPIRED
        )->willReturnSelf();
        $this->historyManagement->expects($this->once())
            ->method('updateStatusLog')
            ->with($quoteId, false, true);

        $this->expiredQuote->execute();
    }

    /**
     * Test for method execute() with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception();
        $this->quote->expects($this->once())->method('getId')->willThrowException($exception);
        $this->expiredQuoteList->expects($this->once())->method('getExpiredQuotes')->willReturn([$this->quote]);
        $this->logger->expects($this->once())->method('critical');

        $this->expiredQuote->execute();
    }
}
