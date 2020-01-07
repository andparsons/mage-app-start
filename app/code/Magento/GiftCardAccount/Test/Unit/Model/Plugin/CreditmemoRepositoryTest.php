<?php
namespace Magento\GiftCardAccount\Test\Unit\Model\Plugin;

use Magento\GiftCardAccount\Model\Plugin\CreditmemoRepository;
use Magento\Sales\Api\Data\CreditmemoExtension;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\GiftCardAccount\Api\GiftCardAccountRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoExtensionInterface;
use Magento\GiftCardAccount\Api\Data\GiftCardAccountSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

/**
 * Unit test for Creditmemo repository plugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreditmemoRepository
     */
    private $plugin;

    /**
     * @var CreditmemoRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var CreditmemoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoMock;

    /**
     * @var float
     */
    private $giftCardsAmount = 10;

    /**
     * @var float
     */
    private $baseGiftCardsAmount = 15;

    /**
     * @var CreditmemoExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeMock;

    /**
     * @var CreditmemoSearchResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoSearchResultMock;

    /**
     * @var CreditmemoExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoExtensionFactoryMock;

    /**
     * @var GiftCardAccountRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $giftCardAccountRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $criteriaBuilderMock;

    /** @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $orderRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->subjectMock = $this->getMockForAbstractClass(
            CreditmemoRepositoryInterface::class
        );

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->setMethods(
                [
                    'getExtensionAttributes',
                    'setExtensionAttributes',
                    'setGiftCardsAmount',
                    'setBaseGiftCardsAmount',
                    'getBaseGiftCardsAmount',
                    'getGiftCardsAmount',
                    'getOrderItemId',
                    'getQty',
                    'getOrder',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->extensionAttributeMock = $this->getMockBuilder(CreditmemoExtensionInterface::class)
            ->setMethods(
                [
                    'getGiftCardsAmount',
                    'getBaseGiftCardsAmount',
                    'setGiftCardsAmount',
                    'setBaseGiftCardsAmount',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoSearchResultMock = $this->getMockForAbstractClass(
            CreditmemoSearchResultInterface::class
        );

        $this->creditmemoExtensionFactoryMock = $this->getMockBuilder(CreditmemoExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->giftCardAccountRepositoryMock = $this->getMockBuilder(GiftCardAccountRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getList',
                    'getItems',
                ]
            )->getMockForAbstractClass();

        $searchCriteriaInterfaceMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->criteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'addFilter',
                    'setPageSize',
                    'create',
                ]
            )->getMock();
        $this->criteriaBuilderMock->method('addFilter')->willReturnSelf();
        $this->criteriaBuilderMock->method('setPageSize')->willReturnSelf();
        $this->criteriaBuilderMock->method('create')->willReturn($searchCriteriaInterfaceMock);

        $searchCriteriaInterfaceMock = $this->getMockBuilder(GiftCardAccountSearchResultInterface::class)
            ->disableOriginalConstructor(
                [
                    'getItems',
                ]
            )
            ->getMock();
        $searchCriteriaInterfaceMock->method('getItems')->willReturn([]);

        $this->giftCardAccountRepositoryMock->method('getList')->willReturn($searchCriteriaInterfaceMock);

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new CreditmemoRepository(
            $this->creditmemoExtensionFactoryMock,
            $this->giftCardAccountRepositoryMock,
            $this->criteriaBuilderMock,
            $this->orderRepositoryMock
        );
    }

    public function testAfterGet()
    {
        $this->creditmemoMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributeMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getGiftCardsAmount')
            ->willReturn($this->giftCardsAmount);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseGiftCardsAmount')
            ->willReturn($this->baseGiftCardsAmount);

        $this->extensionAttributeMock->expects($this->once())
            ->method('setGiftCardsAmount')
            ->with($this->giftCardsAmount)
            ->willReturnSelf();
        $this->extensionAttributeMock->expects($this->once())
            ->method('setBaseGiftCardsAmount')
            ->with($this->baseGiftCardsAmount)
            ->willReturnSelf();

        $this->creditmemoMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributeMock)
            ->willReturnSelf();

        $this->plugin->afterGet($this->subjectMock, $this->creditmemoMock);
    }

    public function testAfterGetList()
    {
        $this->creditmemoSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->creditmemoMock]);

        $this->creditmemoMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributeMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getGiftCardsAmount')
            ->willReturn($this->giftCardsAmount);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseGiftCardsAmount')
            ->willReturn($this->baseGiftCardsAmount);

        $this->extensionAttributeMock->expects($this->once())
            ->method('setGiftCardsAmount')
            ->with($this->giftCardsAmount)
            ->willReturnSelf();
        $this->extensionAttributeMock->expects($this->once())
            ->method('setBaseGiftCardsAmount')
            ->with($this->baseGiftCardsAmount)
            ->willReturnSelf();

        $this->creditmemoMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensionAttributeMock)
            ->willReturnSelf();

        $this->plugin->afterGetList($this->subjectMock, $this->creditmemoSearchResultMock);
    }

    /**
     * Test plugin gift card account after save credit memo
     *
     * @return void
     */
    public function testAfterSave(): void
    {
        $orderMock = $this->getMockBuilder(Order::class)
            ->setMethods(['getItems', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getItemId',
                    'getProductOptionByCode',
                ]
            )
            ->getMock();
        $orderItemMock->method('getItemId')
            ->willReturn(1);
        $orderItemMock->expects($this->any())
            ->method('getProductOptionByCode')
            ->with('giftcard_created_codes')
            ->willReturn(
                [
                    'testcode',
                ]
            );
        $iteratorOrder = new \ArrayIterator([$orderItemMock]);
        $orderMock->method('getItems')
            ->willReturn($iteratorOrder);

        $this->creditmemoMock->method('getOrderItemId')
            ->willReturn(1);
        $this->creditmemoMock->method('getQty')
            ->willReturn(1);

        $iteratorCredit = new \ArrayIterator([$this->creditmemoMock]);
        $this->creditmemoMock->method('getItems')
            ->willReturn($iteratorCredit);

        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->plugin->afterSave($this->subjectMock, $this->creditmemoMock);
    }
}
