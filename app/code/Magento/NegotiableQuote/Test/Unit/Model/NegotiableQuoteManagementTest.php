<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Unit test for NegotiableQuoteManagement model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NegotiableQuoteManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailSender;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteConverter;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteUpdater;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHistory;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteManagement
     */
    private $management;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->emailSender = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Email\Sender::class)
            ->disableOriginalConstructor()->getMock();
        $this->commentManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\CommentManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteItemManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->negotiableQuoteConverter = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuoteConverter::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteUpdater = $this->getMockBuilder(\Magento\NegotiableQuote\Model\QuoteUpdater::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\History::class)
            ->disableOriginalConstructor()->getMock();
        $this->validatorFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->management = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\NegotiableQuoteManagement::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'emailSender' => $this->emailSender,
                'commentManagement' => $this->commentManagement,
                'quoteItemManagement' => $this->quoteItemManagement,
                'negotiableQuoteConverter' => $this->negotiableQuoteConverter,
                'quoteUpdater' => $this->quoteUpdater,
                'quoteHistory' => $this->quoteHistory,
                'validatorFactory' => $this->validatorFactory,
            ]
        );
    }

    /**
     * Test for close method.
     *
     * @return void
     */
    public function testClose()
    {
        $quoteId = 1;
        $quoteSnapshot = ['quote_snapshot_data'];
        list($quote, $negotiableQuote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'close');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $negotiableQuote->expects($this->once())->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_CLOSED)->willReturnSelf();
        $this->quoteHistory->expects($this->once())->method('closeLog')->with($quoteId);
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('quoteToArray')->with($quote)->willReturn($quoteSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')
            ->with(json_encode($quoteSnapshot))->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->assertTrue($this->management->close($quoteId));
    }

    /**
     * Test for close method with validation errors.
     *
     * @return void
     */
    public function testCloseWithValidationErrors()
    {
        $quoteId = 1;
        list($quote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'close');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $this->assertFalse($this->management->close($quoteId));
    }

    /**
     * Test for openByMerchant method.
     *
     * @param int|null $negotiablePriceValue
     * @param string $itemManagementMethod
     * @param array $itemManagementMethodParams
     * @return void
     * @dataProvider openByMerchantDataProvider
     */
    public function testOpenByMerchant($negotiablePriceValue, $itemManagementMethod, array $itemManagementMethodParams)
    {
        $quoteId = 1;
        $quoteSnapshot = ['quote_snapshot_data'];
        list($quote, $negotiableQuote, $quoteExtensionAttributes) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'edit');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $oldData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')->with($quote)->willReturn($oldData);
        $negotiableQuote->expects($this->once())->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_CREATED);
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN)->willReturnSelf();
        $quoteExtensionAttributes->expects($this->once())
            ->method('setNegotiableQuote')->with($negotiableQuote)->willReturnSelf();
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteHistory->expects($this->once())->method('updateStatusLog')->with($quoteId, true);
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('quoteToArray')->with($quote)->willReturn($quoteSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')
            ->with(json_encode($quoteSnapshot))->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $negotiableQuote->expects($this->once())->method('getNegotiatedPriceValue')->willReturn($negotiablePriceValue);
        $this->quoteItemManagement->expects($this->once())
            ->method($itemManagementMethod)->with(...$itemManagementMethodParams)->willReturn(true);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')->with($quote, $oldData)->willReturn($oldData);
        $this->assertTrue($this->management->openByMerchant($quoteId));
    }

    /**
     * Test for openByMerchant method with validation errors.
     *
     * @return void
     */
    public function testOpenByMerchantWithValidationErrors()
    {
        $quoteId = 1;
        list($quote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'edit');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $this->assertFalse($this->management->openByMerchant($quoteId));
    }

    /**
     * Test for send method.
     *
     * @return void
     */
    public function testSend()
    {
        $quoteId = 1;
        $commentText = 'Comment Message';
        $files = ['files_data'];
        $quoteSnapshot = ['quote_snapshot_data'];
        list($quote, $negotiableQuote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'send', $files);
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $negotiableQuote->expects($this->once())->method('getStatus')
            ->willReturn(NegotiableQuoteInterface::STATUS_EXPIRED);
        $negotiableQuote->expects($this->once())->method('setExpirationPeriod')->with(null)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER)->willReturnSelf();
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepository->expects($this->exactly(2))->method('save')->with($quote);
        $this->commentManagement->expects($this->once())
            ->method('update')->with($quoteId, $commentText, $files)->willReturn(true);
        $this->quoteHistory->expects($this->once())->method('updateLog')->with($quoteId);
        $this->emailSender->expects($this->once())->method('sendChangeQuoteEmailToMerchant')->with(
            $quote,
            \Magento\NegotiableQuote\Model\Email\Sender::XML_PATH_SELLER_QUOTE_UPDATED_BY_BUYER_TEMPLATE
        );
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('quoteToArray')->with($quote)->willReturn($quoteSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')
            ->with(json_encode($quoteSnapshot))->willReturnSelf();
        $this->assertTrue($this->management->send($quoteId, $commentText, $files));
    }

    /**
     * Test for send method with validation errors.
     *
     * @return void
     */
    public function testSendWithValidationErrors()
    {
        $quoteId = 1;
        $commentText = 'Comment Message';
        $files = ['files_data'];
        list($quote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'send', $files);
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $this->assertFalse($this->management->send($quoteId, $commentText, $files));
    }

    /**
     * Test for adminSend method.
     *
     * @return void
     */
    public function testAdminSend()
    {
        $quoteId = 1;
        $commentText = 'Comment Message';
        $files = ['files_data'];
        $quoteSnapshot = ['quote_snapshot_data'];
        list($quote, $negotiableQuote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'send', $files);
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('setHasUnconfirmedChanges')->with(false)->willReturnSelf();
        $negotiableQuote->expects($this->atLeastOnce())
            ->method('setIsCustomerPriceChanged')->with(false)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setIsShippingTaxChanged')->with(false)->willReturnSelf();
        $this->quoteUpdater->expects($this->once())->method('updateQuote')->with($quoteId, [])->willReturn(true);
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN)->willReturnSelf();
        $this->quoteRepository->expects($this->exactly(2))->method('save')->with($quote);
        $this->emailSender->expects($this->once())->method('sendChangeQuoteEmailToBuyer')->with(
            $quote,
            \Magento\NegotiableQuote\Model\Email\Sender::XML_PATH_BUYER_QUOTE_UPDATED_BY_SELLER_TEMPLATE
        );
        $this->commentManagement->expects($this->once())
            ->method('update')->with($quoteId, $commentText, $files)->willReturn(true);
        $this->quoteHistory->expects($this->once())->method('updateLog')->with($quoteId);
        $negotiableQuote->expects($this->once())->method('getNegotiatedPriceValue')->willReturn(10);
        $this->quoteHistory->expects($this->once())->method('removeFrontMessage')->with($negotiableQuote);
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('quoteToArray')->with($quote)->willReturn($quoteSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')
            ->with(json_encode($quoteSnapshot))->willReturnSelf();
        $this->assertTrue($this->management->adminSend($quoteId, $commentText, $files));
    }

    /**
     * Test for adminSend method wit hvalidation errors.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Validation error message
     */
    public function testAdminSendWithValidationErrors()
    {
        $quoteId = 1;
        $commentText = 'Comment Message';
        $files = ['files_data'];
        list($quote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'send', $files);
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $validatorResult->expects($this->once())->method('getMessages')->willReturn([__('Validation error message')]);
        $this->management->adminSend($quoteId, $commentText, $files);
    }

    /**
     * Test for updateProcessingByCustomerQuoteStatus method.
     *
     * @return void
     */
    public function testUpdateProcessingByCustomerQuoteStatus()
    {
        $quoteId = 1;
        list($quote, $negotiableQuote) = $this->mockQuote($quoteId);
        $negotiableQuote->expects($this->exactly(2))->method('getStatus')
            ->willReturnOnConsecutiveCalls(
                NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
                NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER
            );
        $validatorResult = $this->mockQuoteValidation($quote, 'edit');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER)->willReturnSelf();
        $this->quoteHistory->expects($this->once())->method('updateStatusLog')->with($quoteId, false);
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->assertEquals(
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
            $this->management->updateProcessingByCustomerQuoteStatus($quoteId, true)
        );
    }

    /**
     * Test for saveAsDraft method.
     *
     * @return void
     */
    public function testSaveAsDraft()
    {
        $quoteId = 1;
        $quoteData = ['quote_data'];
        $commentData = ['message' => 'comment message', 'files' => ['files_list']];
        list($quote) = $this->mockQuote($quoteId);
        $this->quoteUpdater->expects($this->once())
            ->method('updateQuote')->with($quoteId, $quoteData)->willReturn(true);
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->commentManagement->expects($this->once())
            ->method('getFilesNamesList')->with($commentData['files'])->willReturn($commentData['files']);
        $this->commentManagement->expects($this->once())->method('update')
            ->with($quoteId, $commentData['message'], $commentData['files'])
            ->willReturn(true);
        $this->assertEquals($this->management, $this->management->saveAsDraft($quoteId, $quoteData, $commentData));
    }

    /**
     * Test for create method.
     *
     * @return void
     */
    public function testCreate()
    {
        $quoteId = 1;
        $quoteName = 'Quote #1';
        $commentText = 'Comment Message';
        $files = ['files_data'];
        $appliedRuleIds = [2, 3];
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(
                [
                    'collectTotals',
                    'getCustomerId',
                    'getGiftCards',
                    'setGiftCards',
                    'getCouponCode',
                    'setCouponCode',
                    'getAppliedRuleIds',
                ]
            )
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->with($quoteId, ['*'])->willReturn($quote);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $validatorResult = $this->mockQuoteValidation($quote, 'create', $files);
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $quote->expects($this->once())->method('getGiftCards')->willReturn(true);
        $quote->expects($this->once())->method('setGiftCards')->with(null)->willReturnSelf();
        $quote->expects($this->once())->method('getCouponCode')->willReturn(true);
        $quote->expects($this->once())->method('setCouponCode')->with(null)->willReturnSelf();
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteUpdater->expects($this->once())->method('updateCurrentDate')->with($quote)->willReturn($quote);
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $quote->expects($this->once())->method('getAppliedRuleIds')->willReturn($appliedRuleIds);
        $negotiableQuote->expects($this->once())->method('setQuoteId')->with($quoteId)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setIsRegularQuote')->with(true)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setAppliedRuleIds')->with($appliedRuleIds)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_CREATED)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setQuoteName')->with($quoteName)->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->quoteItemManagement->expects($this->once())
            ->method('updateQuoteItemsCustomPrices')->with($quoteId)->willReturn(true);
        $this->commentManagement->expects($this->once())->method('update')
            ->with($quoteId, $commentText, $files)->willReturn(true);
        $this->quoteHistory->expects($this->once())->method('createLog')->with($quoteId);
        $this->emailSender->expects($this->once())->method('sendChangeQuoteEmailToMerchant')->with(
            $quote,
            \Magento\NegotiableQuote\Model\Email\Sender::XML_PATH_SELLER_NEW_QUOTE_CREATED_BY_BUYER_TEMPLATE
        );
        $this->assertTrue($this->management->create($quoteId, $quoteName, $commentText, $files));
    }

    /**
     * Test for create method with validation errors.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Validation error message
     */
    public function testCreateWithValidationErrors()
    {
        $quoteId = 1;
        $quoteName = 'Quote #1';
        $commentText = 'Comment Message';
        $files = ['files_data'];
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteRepository->expects($this->atLeastOnce())
            ->method('get')->with($quoteId, ['*'])->willReturn($quote);
        $validatorResult = $this->mockQuoteValidation($quote, 'create', $files);
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $validatorResult->expects($this->once())->method('getMessages')->willReturn([__('Validation error message')]);
        $this->management->create($quoteId, $quoteName, $commentText, $files);
    }

    /**
     * Test for getSnapshotQuote method.
     *
     * @return void
     */
    public function testGetSnapshotQuote()
    {
        $quoteId = 1;
        $quoteSnapshot = ['quote_snapshot_data'];
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteRepository->expects($this->atLeastOnce())
            ->method('get')->with($quoteId, ['*'])->willReturn($quote);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot'])
            ->getMockForAbstractClass();
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getSnapshot')->willReturn(json_encode($quoteSnapshot));
        $quoteFromSnapshot = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('arrayToQuote')->with($quoteSnapshot)->willReturn($quoteFromSnapshot);
        $this->assertEquals($quoteFromSnapshot, $this->management->getSnapshotQuote($quoteId));
    }

    /**
     * Test for decline method.
     *
     * @return void
     */
    public function testDecline()
    {
        $quoteId = 1;
        $reason = 'Decline reason';
        $quoteSnapshot = ['quote_snapshot_data'];
        list($quote, $negotiableQuote, $quoteExtensionAttributes) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'decline');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $shipping = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $shippingAssignment = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $shippingAssignment->expects($this->once())->method('getShipping')->willReturn($shipping);
        $shipping->expects($this->once())->method('setMethod')->with(null);
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getShippingAssignments')->willReturn([$shippingAssignment]);
        $oldData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')->with($quote)->willReturn($oldData);
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_DECLINED)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setIsCustomerPriceChanged')->with(false)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setHasUnconfirmedChanges')->with(false)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setIsShippingTaxChanged')->with(false)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setNegotiatedPriceType')->with(null)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setNegotiatedPriceValue')->with(null)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setShippingPrice')->with(null)->willReturnSelf();
        $shippingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->setMethods(['setShippingMethod', 'setShippingDescription'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddress);
        $shippingAddress->expects($this->once())->method('setShippingMethod')->with(null)->willReturnSelf();
        $shippingAddress->expects($this->once())->method('setShippingDescription')->with(null)->willReturnSelf();
        $this->quoteItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax')->with($quoteId, true, true)->willReturn(true);
        $this->commentManagement->expects($this->once())
            ->method('update')->with($quoteId, $reason, [])->willReturn(true);
        $this->quoteHistory->expects($this->once())->method('updateLog')
            ->with($quoteId, true, NegotiableQuoteInterface::STATUS_DECLINED);
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('quoteToArray')->with($quote)->willReturn($quoteSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')
            ->with(json_encode($quoteSnapshot))->willReturnSelf();
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')->with($quote, $oldData)->wilLReturn($oldData);
        $this->quoteHistory->expects($this->once())->method('removeAdminMessage')->with($negotiableQuote);
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->assertTrue($this->management->decline($quoteId, $reason));
    }

    /**
     * Test for decline method with validation errors.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Validation error message
     */
    public function testDeclineWithValidationErrors()
    {
        $quoteId = 1;
        $reason = 'Decline reason';
        list($quote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'decline');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $validatorResult->expects($this->once())->method('getMessages')->willReturn([__('Validation error message')]);
        $this->management->decline($quoteId, $reason);
    }

    /**
     * Test for order method.
     *
     * @return void
     */
    public function testOrder()
    {
        $quoteId = 1;
        $quoteSnapshot = ['quote_snapshot_data'];
        list($quote, $negotiableQuote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'checkout');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(false);
        $negotiableQuote->expects($this->once())->method('setStatus')
            ->with(NegotiableQuoteInterface::STATUS_ORDERED)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('getSnapshot')->willReturn(json_encode($quoteSnapshot));
        $negotiableQuote->expects($this->once())->method('setSnapshot')
            ->with(
                json_encode(
                    $quoteSnapshot + [
                        'negotiable_quote' => [
                            NegotiableQuoteInterface::QUOTE_STATUS => NegotiableQuoteInterface::STATUS_ORDERED
                        ]
                    ]
                )
            )->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->quoteHistory->expects($this->once())->method('updateLog')->with($quoteId);
        $this->assertTrue($this->management->order($quoteId));
    }

    /**
     * Test for order method with validation errors.
     *
     * @return void
     */
    public function testOrderWithValidationErrors()
    {
        $quoteId = 1;
        list($quote) = $this->mockQuote($quoteId);
        $validatorResult = $this->mockQuoteValidation($quote, 'checkout');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $this->assertFalse($this->management->order($quoteId));
    }

    /**
     * Test for removeQuoteItem method.
     *
     * @return void
     */
    public function testRemoveQuoteItem()
    {
        $quoteId = 1;
        $itemId = 2;
        list($quote, $negotiableQuote) = $this->mockQuote($quoteId);
        $oldData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')->with($quote)->willReturn($oldData);
        $validatorResult = $this->mockQuoteValidation($quote, 'edit');
        $validatorResult->expects($this->exactly(2))->method('hasMessages')->willReturn(false);
        $quote->expects($this->once())->method('removeItem')->with($itemId)->willReturnSelf();
        $negotiableQuote->expects($this->atLeastOnce())->method('getNegotiatedPriceValue')->willReturn(10);
        $negotiableQuote->expects($this->once())->method('setIsCustomerPriceChanged')->with(true)->willReturnSelf();
        $this->quoteRepository->expects($this->atLeastOnce())->method('save')->with($quote);
        $negotiableQuote->expects($this->once())->method('setHasUnconfirmedChanges')->with(true)->willReturnSelf();
        $this->quoteItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax')->with($quoteId, true, true)->willReturn(true);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')->with($quote, $oldData)->willReturn($oldData);
        $this->assertTrue($this->management->removeQuoteItem($quoteId, $itemId));
    }

    /**
     * Test for removeQuoteItem method with validation errors.
     *
     * @return void
     */
    public function testRemoveQuoteItemWithValidationErrors()
    {
        $quoteId = 1;
        $itemId = 2;
        list($quote) = $this->mockQuote($quoteId);
        $oldData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')->with($quote)->willReturn($oldData);
        $validatorResult = $this->mockQuoteValidation($quote, 'edit');
        $validatorResult->expects($this->once())->method('hasMessages')->willReturn(true);
        $this->assertFalse($this->management->removeQuoteItem($quoteId, $itemId));
    }

    /**
     * Test for removeNegotiation method.
     *
     * @return void
     */
    public function testRemoveNegotiation()
    {
        $quoteId = 1;
        $quoteSnapshot = ['quote_snapshot_data'];
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->with($quoteId)->willReturn($quote);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSnapshot'])
            ->getMockForAbstractClass();
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $oldData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')->with($quote)->willReturn($oldData);
        $negotiableQuote->expects($this->once())->method('setNegotiatedPriceType')->with(null)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setNegotiatedPriceValue')->with(null)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setShippingPrice')->with(null)->willReturnSelf();
        $this->quoteItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax')->with($quoteId, true, true, false)->willReturn(true);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')->with($quote, $oldData)->willReturn($oldData);
        $this->quoteHistory->expects($this->once())->method('updateLog')->with($quoteId, true);
        $this->negotiableQuoteConverter->expects($this->once())
            ->method('quoteToArray')->with($quote)->willReturn($quoteSnapshot);
        $negotiableQuote->expects($this->once())->method('setSnapshot')
            ->with(json_encode($quoteSnapshot))->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->management->removeNegotiation($quoteId);
    }

    /**
     * Test for recalculateQuote method.
     *
     * @param bool $updatePrice
     * @param string $itemManagementMethod
     * @param array $itemManagementMethodParams
     * @return void
     * @dataProvider recalculateQuoteDataProvider
     */
    public function testRecalculateQuote($updatePrice, $itemManagementMethod, array $itemManagementMethodParams)
    {
        $quoteId = 1;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($quote);
        $oldData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getIsTaxChanged', 'getIsPriceChanged', 'getIsDiscountChanged'])
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')->with($quote)->willReturn($oldData);
        $this->quoteItemManagement->expects($this->once())
            ->method($itemManagementMethod)->with(...$itemManagementMethodParams)->willReturn(true);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')->with($quote, $oldData)->willReturn($oldData);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $oldData->expects($this->once())->method('getIsTaxChanged')->willReturn(false);
        $oldData->expects($this->once())->method('getIsPriceChanged')->willReturn(false);
        $oldData->expects($this->once())->method('getIsDiscountChanged')->willReturn(true);
        $negotiableQuote->expects($this->once())
            ->method('getStatus')->willReturn(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
        $negotiableQuote->expects($this->once())->method('getNegotiatedPriceValue')->willReturn(10);
        $negotiableQuote->expects($this->once())->method('setIsCustomerPriceChanged')->with(true)->willReturnSelf();
        $negotiableQuote->expects($this->once())->method('setIsAddressDraft')->with(false)->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->management->recalculateQuote($quoteId, $updatePrice);
    }

    /**
     * Test for updateQuoteItems method.
     *
     * @return void
     */
    public function testUpdateQuoteItems()
    {
        $quoteId = 1;
        $cartData = [];
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->with($quoteId)->willReturn($quote);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()->getMock();
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $oldData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getIsTaxChanged', 'getIsPriceChanged', 'getIsDiscountChanged'])
            ->disableOriginalConstructor()->getMock();
        $this->quoteHistory->expects($this->once())
            ->method('collectOldDataFromQuote')->with($quote)->willReturn($oldData);
        $this->quoteUpdater->expects($this->once())
            ->method('updateQuoteItemsByCartData')->with($quote, $cartData)->willReturn($quote);
        $this->quoteItemManagement->expects($this->once())
            ->method('recalculateOriginalPriceTax')->with($quoteId, true, true)->willReturn(true);
        $this->quoteHistory->expects($this->once())
            ->method('checkPricesAndDiscounts')->with($quote, $oldData)->willReturn($oldData);
        $negotiableQuote->expects($this->once())->method('getIsCustomerPriceChanged')->willReturn(true);
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);
        $this->management->updateQuoteItems($quoteId, $cartData);
    }

    /**
     * Test for getNegotiableQuote method without quote.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested quote is not found. Row ID: quoteId = 1
     */
    public function testGetNegotiableQuoteWithoutQuote()
    {
        $quoteId = 1;
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId, ['*'])
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Exception message')));
        $this->management->getNegotiableQuote($quoteId);
    }

    /**
     * Test for getNegotiableQuote method without negotiable quote.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested quote is not found. Row ID: quoteId = 1
     */
    public function testGetNegotiableQuoteWithoutNegotiableQuote()
    {
        $quoteId = 1;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId, ['*'])->willReturn($quote);
        $this->management->getNegotiableQuote($quoteId);
    }

    /**
     * Data provider for testOpenByMerchant.
     *
     * @return array
     */
    public function openByMerchantDataProvider()
    {
        return [
            [null, 'recalculateOriginalPriceTax', [1, true, true]],
            [10, 'recalculateOriginalPriceTax', [1, false, false]],
        ];
    }

    /**
     * Data provider for testRecalculateQuote.
     *
     * @return array
     */
    public function recalculateQuoteDataProvider()
    {
        return [
            [true, 'recalculateOriginalPriceTax', [1, true, true, false]],
            [false, 'recalculateOriginalPriceTax', [1, false, false, false]],
        ];
    }

    /**
     * Create mocks for quote validation.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $quote
     * @param string $action
     * @param array $files [optional]
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockQuoteValidation(\PHPUnit_Framework_MockObject_MockObject $quote, $action, array $files = [])
    {
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->validatorFactory->expects($this->atLeastOnce())
            ->method('create')->with(['action' => $action])->willReturn($validator);
        $validatorResult = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()->getMock();
        $validator->expects($this->atLeastOnce())->method('validate')
            ->with(['quote' => $quote] + (!empty($files) ? ['files' => $files] : []))
            ->willReturn($validatorResult);
        return $validatorResult;
    }

    /**
     * Create mock of quote.
     *
     * @param int $quoteId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockQuote($quoteId)
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['collectTotals', 'getCustomerId', 'getShippingAddress', 'removeItem'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->with($quoteId, ['*'])->willReturn($quote);
        $quoteExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote', 'getShippingAssignments'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSnapshot', 'getSnapshot'])
            ->getMockForAbstractClass();
        $quoteExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        return [$quote, $negotiableQuote, $quoteExtensionAttributes];
    }
}
