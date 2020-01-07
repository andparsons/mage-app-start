<?php

namespace Magento\RequisitionList\Test\Unit\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\RequisitionList\Model\RequisitionListItem\CartItemConverter;

/**
 * Unit test for RequisitionListManagement.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequisitionListManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var RequisitionListItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepository;

    /**
     * @var CartItemConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemConverter;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Validation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validation;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Merger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMerger;

    /**
     * @var \Magento\RequisitionList\Api\Data\RequisitionListItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItem;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTime;

    /**
     * @var \Magento\RequisitionList\Model\AddToCartProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addToCartProcessor;

    /**
     * @var \Magento\RequisitionList\Model\AddToCartProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $giftCardAddToCartProcessor;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListManagement
     */
    private $requisitionListManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requisitionListRepository = $this->getMockBuilder(RequisitionListRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemFactory = $this->getMockBuilder(RequisitionListItemInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cartItemConverter = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\CartItemConverter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validation = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Validation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMerger = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Merger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addToCartProcessor = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\AddToCartProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->giftCardAddToCartProcessor = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\AddToCartProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $addToCartProcessors = [
            'simple' => $this->addToCartProcessor,
            'giftcard' => $this->giftCardAddToCartProcessor
        ];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requisitionListManagement = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListManagement::class,
            [
                'requisitionListRepository' => $this->requisitionListRepository,
                'requisitionListItemFactory' => $this->requisitionListItemFactory,
                'cartRepository' => $this->cartRepository,
                'cartItemConverter' => $this->cartItemConverter,
                'validation' => $this->validation,
                'itemMerger' => $this->itemMerger,
                'dateTime' => $this->dateTime,
                'addToCartProcessors' => $addToCartProcessors,
            ]
        );
    }

    /**
     * Test for addItemToList method.
     *
     * @return void
     */
    public function testAddItemToList()
    {
        $requisitionList = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requisitionList->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->itemMerger->expects($this->atLeastOnce())
            ->method('mergeItem')->with([], $this->requisitionListItem)->willReturn([$this->requisitionListItem]);
        $requisitionList->expects($this->once())
            ->method('setItems')->with([$this->requisitionListItem])->willReturnSelf();
        $this->requisitionListRepository->expects($this->once())
            ->method('save')->with($requisitionList)->willReturnSelf();
        $this->assertEquals(
            $requisitionList,
            $this->requisitionListManagement->addItemToList($requisitionList, $this->requisitionListItem)
        );
    }

    /**
     * Test for setItemsToList method.
     *
     * @return void
     */
    public function testSetItemsToList()
    {
        $requisitionList = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->itemMerger->expects($this->atLeastOnce())
            ->method('merge')->with([$this->requisitionListItem])->willReturnArgument(0);
        $requisitionList->expects($this->once())
            ->method('setItems')->with([$this->requisitionListItem])->willReturnSelf();

        $this->assertEquals(
            $requisitionList,
            $this->requisitionListManagement->setItemsToList($requisitionList, [$this->requisitionListItem])
        );
    }

    /**
     * Test for copyItemToList method.
     *
     * @return void
     */
    public function testCopyItemToList()
    {
        $requisitionList = $this->getMockBuilder(RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requisitionList->expects($this->atLeastOnce())->method('getItems')->willReturn([]);

        $listItem = $this->getMockBuilder(RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->requisitionListItem);

        $this->itemMerger->expects($this->atLeastOnce())
            ->method('mergeItem')->with([], $this->requisitionListItem)->willReturn([$this->requisitionListItem]);
        $requisitionList->expects($this->once())
            ->method('setItems')->with([$this->requisitionListItem])->willReturnSelf();

        $this->assertEquals(
            $requisitionList,
            $this->requisitionListManagement->copyItemToList($requisitionList, $listItem)
        );
    }

    /**
     * Test for placeItemsInCart method.
     *
     * @param string $productType
     * @param int $productsAddedToCart
     * @param int $giftCardsAddedToCart
     * @return void
     * @dataProvider placeItemInCartDataProvider
     */
    public function testPlaceItemsInCart($productType, $productsAddedToCart, $giftCardsAddedToCart)
    {
        $listId = 1;

        $this->defaultAddToCartProcessorKey = $productType;
        $cart = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'removeAllItems', 'collectTotals', 'addProduct'])
            ->getMockForAbstractClass();
        $cart->expects($this->any())->method('getId')->willReturn(1);
        $cart->expects($this->any())->method('addProduct')->willReturnSelf();
        $cart->expects($this->atLeastOnce())->method('removeAllItems')->willReturnSelf();
        $this->cartRepository->expects($this->atLeastOnce())->method('get')->willReturn($cart);
        $this->validation->expects($this->atLeastOnce())->method('isValid')->willReturn(true);
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMockForAbstractClass();

        $this->cartItemConverter->expects($this->atLeastOnce())->method('convert')->willReturn($cartItem);
        $product->expects($this->atLeastOnce())->method('getTypeId')->willReturn($productType);
        $cartItem->expects($this->atLeastOnce())->method('getData')->with('product')->willReturn($product);

        $this->requisitionListItem->expects($this->atLeastOnce())
            ->method('getRequisitionListId')
            ->willReturn($listId);

        $list = $this->getMockBuilder(RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requisitionListRepository->expects($this->atLeastOnce())
            ->method('get')
            ->with($listId)
            ->willReturn($list);
        $list->expects($this->atLeastOnce())
            ->method('setUpdatedAt')
            ->willReturnSelf();
        $this->requisitionListRepository->expects($this->atLeastOnce())
            ->method('save')
            ->with($list)
            ->willReturn($list);
        $this->addToCartProcessor->expects($this->exactly($productsAddedToCart))->method('execute')
            ->with($cart, $cartItem);
        $this->giftCardAddToCartProcessor->expects($this->exactly($giftCardsAddedToCart))->method('execute')
            ->with($cart, $cartItem);

        $this->assertEquals(
            [$this->requisitionListItem],
            $this->requisitionListManagement->placeItemsInCart(1, [$this->requisitionListItem], true)
        );
    }

    /**
     * DataProvider for testPlaceItemsInCart().
     *
     * @return array
     */
    public function placeItemInCartDataProvider()
    {
        return [
            ['simple', 1, 0],
            ['giftcard', 0, 1]
        ];
    }
}
