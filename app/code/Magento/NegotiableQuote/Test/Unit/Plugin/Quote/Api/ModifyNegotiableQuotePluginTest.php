<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Api;

/**
 * Test for Magento\NegotiableQuote\Plugin\Quote\Api\ModifyNegotiableQuotePlugin class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModifyNegotiableQuotePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCollectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $result;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHistory;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Api\ModifyNegotiableQuotePlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quoteCollectionFactory = $this->getMockBuilder(
            \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->validatorFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteRepository = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemsCount'])
            ->getMockForAbstractClass();
        $this->result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteItemManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteHistory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\History::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Api\ModifyNegotiableQuotePlugin::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'validatorFactory' => $this->validatorFactory,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'quoteCollectionFactory' => $this->quoteCollectionFactory,
                'negotiableQuoteItemManagement' => $this->negotiableQuoteItemManagement,
                'quoteHistory' => $this->quoteHistory,
                'oldQuoteData' => [1 => $data],
            ]
        );
    }

    /**
     * Test beforeDeleteById method.
     *
     * @return void
     */
    public function testBeforeDeleteById()
    {
        $quoteId = 1;
        $itemId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->prepareMocksForBeforeMethods();
        $this->result->expects($this->once())->method('hasMessages')->willReturn(false);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(3);

        $this->plugin->beforeDeleteById($subject, $quoteId, $itemId);
    }

    /**
     * Test beforeDeleteById method with 1 item in quote.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Cannot delete all items from a B2B quote. The quote must contain at least one item.
     */
    public function testBeforeDeleteByIdWithOneItemInQuote()
    {
        $quoteId = 1;
        $itemId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->prepareMocksForBeforeMethods();
        $this->result->expects($this->once())->method('hasMessages')->willReturn(false);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(1);

        $this->plugin->beforeDeleteById($subject, $quoteId, $itemId);
    }

    /**
     * Test beforeDeleteById method with invalid quote status.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The quote 1 is currently locked and cannot be updated. Please check the quote status.
     */
    public function testBeforeDeleteByIdWithInvalidQuoteStatus()
    {
        $quoteId = 1;
        $itemId = 1;
        $message = __(
            'The quote %quoteId is currently locked and cannot be updated. Please check the quote status.',
            ['quoteId' => 1]
        );
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->prepareMocksForBeforeMethods();
        $this->result->expects($this->once())->method('hasMessages')->willReturn(true);
        $this->result->expects($this->once())->method('getMessages')->willReturn([$message]);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(3);

        $this->plugin->beforeDeleteById($subject, $quoteId, $itemId);
    }

    /**
     * Test before save.
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartItem->expects($this->once())->method('getQuoteId')->willReturn($quoteId);
        $this->prepareMocksForBeforeMethods();
        $this->result->expects($this->once())->method('hasMessages')->willReturn(false);

        $this->plugin->beforeSave($subject, $cartItem);
    }

    /**
     * Test beforeSave method with invalid quote status.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The quote 1 is currently locked and cannot be updated. Please check the quote status.
     */
    public function testBeforeSaveWithInvalidQuoteStatus()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = __(
            'The quote %quoteId is currently locked and cannot be updated. Please check the quote status.',
            ['quoteId' => 1]
        );
        $cartItem->expects($this->once())->method('getQuoteId')->willReturn($quoteId);
        $this->prepareMocksForBeforeMethods();
        $this->result->expects($this->once())->method('hasMessages')->willReturn(true);
        $this->result->expects($this->once())->method('getMessages')->willReturn([$message]);

        $this->plugin->beforeSave($subject, $cartItem);
    }

    /**
     * Test afterDeleteById method.
     *
     * @return void
     */
    public function testAfterDeleteById()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $data = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($this->quote);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $negotiableQuote->expects($this->once())
            ->method('setStatus')
            ->with(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN)
            ->willReturnSelf();
        $negotiableQuote->expects($this->atLeastOnce())->method('getQuoteId')->willReturn($quoteId);
        $this->negotiableQuoteItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax')
            ->with(1, true, true)
            ->willReturn(true);
        $this->quoteHistory->expects($this->once())->method('updateStatusLog')->with(1, true);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')
            ->with($this->quote, $data)
            ->willReturn($data);
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('save')
            ->with($negotiableQuote)
            ->willReturn(true);

        $this->assertTrue($this->plugin->afterDeleteById($subject, true, $quoteId));
    }

    /**
     * Test afterSave method.
     *
     * @return void
     */
    public function testAfterSave()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $data = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cartItem->expects($this->once())->method('getQuoteId')->willReturn($quoteId);
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($this->quote);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $negotiableQuote->expects($this->once())
            ->method('setStatus')
            ->with(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN)
            ->willReturnSelf();
        $negotiableQuote->expects($this->atLeastOnce())->method('getQuoteId')->willReturn($quoteId);
        $this->negotiableQuoteItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax')
            ->with(1, true, true)
            ->willReturn(true);
        $this->quoteHistory->expects($this->once())->method('updateStatusLog')->with(1, true);
        $this->quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')
            ->with($this->quote, $data)
            ->willReturn($data);
        $this->negotiableQuoteRepository->expects($this->once())
            ->method('save')
            ->with($negotiableQuote)
            ->willReturn(true);

        $this->assertEquals($result, $this->plugin->afterSave($subject, $result, $cartItem));
    }

    /**
     * Prepare mocks for "before" methods.
     *
     * @return void
     */
    private function prepareMocksForBeforeMethods()
    {
        $quoteId = 1;
        $quoteCollection = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldQuoteData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($this->quote);
        $this->quoteCollectionFactory->expects($this->once())->method('create')->willReturn($quoteCollection);
        $quoteCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('entity_id', $quoteId)
            ->willReturnSelf();
        $quoteCollection->expects($this->once())->method('getFirstItem')->willReturn($this->quote);
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')
            ->with($this->quote)
            ->willReturn($oldQuoteData);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $this->validatorFactory->expects($this->once())
            ->method('create')
            ->with(['action' => 'edit'])
            ->willReturn($validator);
        $validator->expects($this->once())
            ->method('validate')
            ->with(['quote' => $this->quote])
            ->willReturn($this->result);
    }
}
