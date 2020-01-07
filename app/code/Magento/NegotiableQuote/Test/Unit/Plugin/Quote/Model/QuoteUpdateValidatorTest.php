<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model;

/**
 * Test for Magento\NegotiableQuote\Plugin\Quote\Model\QuoteUpdateValidator class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteUpdateValidatorTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Quote\Api\Data\CartExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartExtensionFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initialQuote;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuote;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $result;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Model\QuoteUpdateValidator
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
        $this->cartExtensionFactory = $this->getMockBuilder(
            \Magento\Quote\Api\Data\CartExtensionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getCreatedAt', 'getStoreId', 'getShippingAddress'])
            ->getMockForAbstractClass();
        $this->initialQuote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getCreatedAt', 'getStoreId'])
            ->getMockForAbstractClass();
        $this->negotiableQuote = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->result = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Model\QuoteUpdateValidator::class,
            [
                'quoteCollectionFactory' => $this->quoteCollectionFactory,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'validatorFactory' => $this->validatorFactory,
                'cartExtensionFactory' => $this->cartExtensionFactory,
            ]
        );
    }

    /**
     * Test beforeSave method.
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->prepareMocks();
        $this->result->expects($this->once())->method('getMessages')->willReturn([]);
        $this->quote->expects($this->atLeastOnce())->method('getCreatedAt')->willReturn('2017-04-05 11:59:59');
        $this->initialQuote->expects($this->once())->method('getCreatedAt')->willReturn('2017-04-05 11:59:59');
        $this->quote->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getCustomerId')->willReturn(1);
        $this->quote->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->negotiableQuote->expects($this->once())->method('getShippingPrice')->willReturn(null);

        $this->plugin->beforeSave($subject, $this->quote);
    }

    /**
     * Test beforeSave without quote id.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "id" is required. Enter and try again.
     */
    public function testBeforeSaveWithoutQuoteId()
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->getMockForAbstractClass();
        $this->quote->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($this->negotiableQuote);

        $this->plugin->beforeSave($subject, $this->quote);
    }

    /**
     * Test beforeSave without invalid negotiable quote id.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage You cannot update the requested attribute. Row ID: quote_id = 2.
     */
    public function testBeforeSaveWithInvalidNegotiableQuoteId()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->getMockForAbstractClass();
        $this->quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($this->negotiableQuote);
        $this->negotiableQuote->expects($this->atLeastOnce())->method('getQuoteId')->willReturn(2);

        $this->plugin->beforeSave($subject, $this->quote);
    }

    /**
     * Test beforeSave with updating attribute that is not allowed to change.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage You cannot update the requested attribute. Row ID: created_at = 2017-04-05 11:59:59.
     */
    public function testBeforeSaveWithInvalidAttribute()
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->prepareMocks();
        $this->result->expects($this->once())->method('getMessages')->willReturn([]);
        $this->quote->expects($this->atLeastOnce())->method('getCreatedAt')->willReturn('2017-04-05 11:59:59');
        $this->initialQuote->expects($this->once())->method('getCreatedAt')->willReturn('2017-04-03 00:00:00');
        $this->quote->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getCustomerId')->willReturn(1);
        $this->quote->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->negotiableQuote->expects($this->once())->method('getShippingPrice')->willReturn(null);

        $this->plugin->beforeSave($subject, $this->quote);
    }

    /**
     * Test beforeSave with shipping price but without shipping address.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Cannot set the shipping price. You must select a shipping method first.
     */
    public function testBeforeSaveWithShippingAndWithoutShippingAddress()
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->prepareMocks();
        $this->result->expects($this->once())->method('getMessages')->willReturn([]);
        $this->quote->expects($this->atLeastOnce())->method('getCreatedAt')->willReturn('2017-04-05 11:59:59');
        $this->initialQuote->expects($this->once())->method('getCreatedAt')->willReturn('2017-04-05 11:59:59');
        $this->quote->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getCustomerId')->willReturn(1);
        $this->quote->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->negotiableQuote->expects($this->once())->method('getShippingPrice')->willReturn(15);
        $this->quote->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($address);
        $address->expects($this->once())->method('getShippingMethod')->willReturn(null);

        $this->plugin->beforeSave($subject, $this->quote);
    }

    /**
     * Test beforeSave with incorrect quote status.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The quote 1 is currently locked and cannot be updated. Please check the quote status.
     */
    public function testBeforeSaveWithIncorrectQuoteStatus()
    {
        $subject = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $message = __(
            'The quote %quoteId is currently locked and cannot be updated. Please check the quote status.',
            ['quoteId' => 1]
        );
        $this->prepareMocks();
        $this->result->expects($this->once())->method('getMessages')->willReturn([$message]);
        $this->quote->expects($this->atLeastOnce())->method('getCreatedAt')->willReturn('2017-04-05 11:59:59');
        $this->initialQuote->expects($this->once())->method('getCreatedAt')->willReturn('2017-04-05 11:59:59');
        $this->quote->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getCustomerId')->willReturn(1);
        $this->quote->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $this->initialQuote->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->negotiableQuote->expects($this->once())->method('getShippingPrice')->willReturn(null);

        $this->plugin->beforeSave($subject, $this->quote);
    }

    /**
     * Prepare mocks.
     *
     * @return void
     */
    private function prepareMocks()
    {
        $quoteId = 1;
        $oldExtensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'setNegotiableQuote'])
            ->getMockForAbstractClass();
        $oldNegotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteCollection = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Validator\ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $this->quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($this->negotiableQuote);
        $this->negotiableQuote->expects($this->atLeastOnce())->method('getQuoteId')->willReturn($quoteId);
        $this->quoteCollectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($quoteCollection);
        $quoteCollection->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->with('entity_id', $quoteId)
            ->willReturnSelf();
        $quoteCollection->expects($this->atLeastOnce())->method('getFirstItem')->willReturn($this->initialQuote);
        $this->negotiableQuoteRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->with($quoteId)
            ->willReturn($oldNegotiableQuote);
        $this->initialQuote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturnOnConsecutiveCalls(
                null,
                $oldExtensionAttributes,
                $oldExtensionAttributes,
                null,
                $oldExtensionAttributes,
                $oldExtensionAttributes
            );
        $this->cartExtensionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($oldExtensionAttributes);
        $this->initialQuote->expects($this->atLeastOnce())
            ->method('setExtensionAttributes')
            ->with($oldExtensionAttributes)
            ->willReturnSelf();
        $oldExtensionAttributes->expects($this->atLeastOnce())
            ->method('setNegotiableQuote')
            ->with($oldNegotiableQuote)
            ->willReturnSelf();
        $oldExtensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($oldNegotiableQuote);
        $oldNegotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $this->negotiableQuote->expects($this->once())->method('setQuoteId')->with($quoteId)->willReturnSelf();
        $this->negotiableQuote->expects($this->once())->method('getStatus')->willReturn(null);
        $oldNegotiableQuote->expects($this->once())
            ->method('getStatus')
            ->willReturn(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_CREATED);
        $this->negotiableQuote->expects($this->once())
            ->method('setStatus')
            ->with(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_CREATED)
            ->willReturnSelf();
        $this->validatorFactory->expects($this->once())
            ->method('create')
            ->with(['action' => 'edit'])
            ->willReturn($validator);
        $validator->expects($this->once())
            ->method('validate')
            ->with(['quote' => $this->initialQuote])
            ->willReturn($this->result);
    }
}
