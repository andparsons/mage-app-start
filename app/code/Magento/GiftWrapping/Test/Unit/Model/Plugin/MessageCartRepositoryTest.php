<?php
namespace Magento\GiftWrapping\Test\Unit\Model\Plugin;

use Magento\GiftWrapping\Model\Plugin\MessageCartRepository as MessageCartRepositoryPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\GiftWrapping\Model\WrappingFactory;
use Magento\GiftWrapping\Helper\Data as DataHelper;
use Magento\GiftMessage\Api\CartRepositoryInterface;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\Data\MessageExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\GiftWrapping\Model\Wrapping;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

class MessageCartRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MessageCartRepositoryPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var QuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var WrappingFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrappingFactoryMock;

    /**
     * @var DataHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataHelperMock;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $giftMessageMock;

    /**
     * @var MessageExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesMock;

    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Wrapping|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrappingMock;

    /**
     * @var QuoteAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteAddressMock;

    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->getMockBuilder(QuoteRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->wrappingFactoryMock = $this->getMockBuilder(WrappingFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder(DataHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->giftMessageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMockForAbstractClass();
        $this->extensionAttributesMock = $this->getMockBuilder(MessageExtensionInterface::class)
            ->setMethods(['getWrappingId', 'getWrappingAllowGiftReceipt', 'getWrappingAddPrintedCard'])
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder(CartInterface::class)
            ->setMethods(['getShippingAddress', 'addData', 'save'])
            ->getMockForAbstractClass();
        $this->wrappingMock = $this->getMockBuilder(Wrapping::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteAddressMock = $this->getMockBuilder(QuoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteRepositoryMock->expects(static::atLeastOnce())
            ->method('getActive')
            ->willReturn($this->quoteMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            MessageCartRepositoryPlugin::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'wrappingFactory' => $this->wrappingFactoryMock,
                'dataHelper' => $this->dataHelperMock
            ]
        );
    }

    public function testAfterSave()
    {
        $wrappingId = 1;
        $wrappingAllowGiftReceipt = false;
        $wrappingAddPrintedCard = true;
        $wrappingLoadedId = 101;
        $wrappingInfo = [
            'gw_id' => $wrappingLoadedId,
            'gw_allow_gift_receipt' => $wrappingAllowGiftReceipt,
            'gw_add_card' => $wrappingAddPrintedCard
        ];

        $this->giftMessageMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->dataHelperMock->expects(static::atLeastOnce())
            ->method('isGiftWrappingAvailableForOrder')
            ->willReturn(true);
        $this->dataHelperMock->expects(static::atLeastOnce())
            ->method('allowGiftReceipt')
            ->willReturn(true);
        $this->dataHelperMock->expects(static::atLeastOnce())
            ->method('allowPrintedCard')
            ->willReturn(true);
        $this->extensionAttributesMock->expects(static::atLeastOnce())
            ->method('getWrappingId')
            ->willReturn($wrappingId);
        $this->extensionAttributesMock->expects(static::atLeastOnce())
            ->method('getWrappingAllowGiftReceipt')
            ->willReturn($wrappingAllowGiftReceipt);
        $this->extensionAttributesMock->expects(static::atLeastOnce())
            ->method('getWrappingAddPrintedCard')
            ->willReturn($wrappingAddPrintedCard);
        $this->wrappingFactoryMock->expects(static::atLeastOnce())
            ->method('create')
            ->willReturn($this->wrappingMock);
        $this->wrappingMock->expects(static::atLeastOnce())
            ->method('load')
            ->with($wrappingId, null)
            ->willReturnSelf();
        $this->wrappingMock->expects(static::atLeastOnce())
            ->method('getId')
            ->willReturn($wrappingLoadedId);
        $this->quoteMock->expects(static::atLeastOnce())
            ->method('getShippingAddress')
            ->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects(static::once())
            ->method('addData')
            ->with($wrappingInfo)
            ->willReturnSelf();
        $this->quoteMock->expects(static::once())
            ->method('addData')
            ->with($wrappingInfo)
            ->willReturnSelf();
        $this->quoteMock->expects(static::once())
            ->method('save')
            ->willReturnSelf();

        $this->assertTrue($this->plugin->afterSave($this->subjectMock, true, 8, $this->giftMessageMock));
    }

    public function testAfterSaveAllOptionsDisabled()
    {
        $this->giftMessageMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->dataHelperMock->expects(static::atLeastOnce())
            ->method('isGiftWrappingAvailableForOrder')
            ->willReturn(false);
        $this->dataHelperMock->expects(static::atLeastOnce())
            ->method('allowGiftReceipt')
            ->willReturn(false);
        $this->dataHelperMock->expects(static::atLeastOnce())
            ->method('allowPrintedCard')
            ->willReturn(false);
        $this->extensionAttributesMock->expects(static::never())
            ->method('getWrappingId');
        $this->extensionAttributesMock->expects(static::never())
            ->method('getWrappingAllowGiftReceipt');
        $this->extensionAttributesMock->expects(static::never())
            ->method('getWrappingAddPrintedCard');
        $this->wrappingFactoryMock->expects(static::never())
            ->method('create');
        $this->quoteMock->expects(static::never())
            ->method('save');

        $this->assertTrue($this->plugin->afterSave($this->subjectMock, true, 8, $this->giftMessageMock));
    }

    public function testAfterSaveNoExtensionAttributes()
    {
        $this->giftMessageMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->dataHelperMock->expects(static::never())
            ->method('isGiftWrappingAvailableForOrder');
        $this->dataHelperMock->expects(static::never())
            ->method('allowGiftReceipt');
        $this->dataHelperMock->expects(static::never())
            ->method('allowPrintedCard');
        $this->extensionAttributesMock->expects(static::never())
            ->method('getWrappingId');
        $this->extensionAttributesMock->expects(static::never())
            ->method('getWrappingAllowGiftReceipt');
        $this->extensionAttributesMock->expects(static::never())
            ->method('getWrappingAddPrintedCard');
        $this->wrappingFactoryMock->expects(static::never())
            ->method('create');
        $this->quoteMock->expects(static::never())
            ->method('save');

        $this->assertTrue($this->plugin->afterSave($this->subjectMock, true, 8, $this->giftMessageMock));
    }
}
