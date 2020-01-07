<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Unit test for NegotiableQuotePriceManagement model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NegotiableQuotePriceManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuotItemManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHistory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCollectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuotePriceManagement
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->negotiableQuoteRepository = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validatorFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuotItemManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteRepository = $this->getMockBuilder(
            \Magento\Quote\Api\CartRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->restriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteHistory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\History::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteCollectionFactory = $this->getMockBuilder(
            \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\NegotiableQuotePriceManagement::class,
            [
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'validatorFactory' => $this->validatorFactory,
                'negotiableQuotItemManagement' => $this->negotiableQuotItemManagement,
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $this->restriction,
                'quoteHistory' => $this->quoteHistory,
                'quoteCollectionFactory' => $this->quoteCollectionFactory,
            ]
        );
    }

    /**
     * Test get method.
     *
     * @return void
     */
    public function testPricesUpdated()
    {
        $quoteIds = [1];
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validateResult = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getQuoteId'])
            ->getMockForAbstractClass();
        $quoteCollection = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteData = new \Magento\Framework\DataObject();
        $this->validatorFactory->expects($this->once())
            ->method('create')
            ->with(['action' => 'edit'])
            ->willReturn($validator);
        $this->quoteRepository->expects($this->exactly(2))->method('get')->with(1)->willReturn($quote);
        $this->restriction->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $validator->expects($this->once())->method('validate')->with(['quote' => $quote])->willReturn($validateResult);
        $validateResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $quote->expects($this->once())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $quoteExtensionAttributes->expects($this->once())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $this->quoteCollectionFactory->expects($this->once())->method('create')->willReturn($quoteCollection);
        $quoteCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('entity_id', ['in' => $quoteIds])
            ->willReturnSelf();
        $quoteCollection->expects($this->once())->method('getItems')->willReturn([$quote]);
        $quote->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')
            ->with($quote)
            ->willReturn($quoteData);
        $negotiableQuote->expects($this->once())
            ->method('setStatus')
            ->with(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN)
            ->willReturnSelf();
        $negotiableQuote->expects($this->exactly(3))->method('getId')->willReturn(1);
        $negotiableQuote->expects($this->once())->method('getQuoteId')->willReturn(1);
        $this->negotiableQuotItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax')
            ->with(1, true, true)
            ->willReturn(true);
        $this->quoteHistory->expects($this->once())->method('updateStatusLog')->with(1, true);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')
            ->with($quote, $quoteData)
            ->willReturn($quoteData);
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('save')
            ->with($negotiableQuote)
            ->willReturn(true);

        $this->assertTrue($this->model->pricesUpdated($quoteIds));
    }

    /**
     * Test get method if quote doesn't exist.
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Requested quote is not found. Row ID: QuoteID = 9999
     * @return void
     */
    public function testGetWithInvalidQuoteId()
    {
        $quoteIds = [9999];
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validatorFactory->expects($this->once())
            ->method('create')
            ->with(['action' => 'edit'])
            ->willReturn($validator);
        $this->quoteRepository->expects($this->once())->method('get')->with(9999)->willThrowException($exception);

        $this->model->pricesUpdated($quoteIds);
    }

    /**
     * Test get method if quote is locked.
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The quote 1 is currently locked and cannot be updated. Please check the quote status.
     * @return void
     */
    public function testGetWithInvalidQuoteStatus()
    {
        $quoteIds = [1];
        $message = __(
            "The quote %quoteId is currently locked and cannot be updated. Please check the quote status.",
            ['quoteId' => 1]
        );
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validateResult = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorFactory->expects($this->once())
            ->method('create')
            ->with(['action' => 'edit'])
            ->willReturn($validator);
        $this->quoteRepository->expects($this->once())->method('get')->with(1)->willReturn($quote);
        $this->restriction->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $validator->expects($this->once())->method('validate')->with(['quote' => $quote])->willReturn($validateResult);
        $validateResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $validateResult->expects($this->once())->method('getMessages')->willReturn([$message]);

        $this->model->pricesUpdated($quoteIds);
    }
}
