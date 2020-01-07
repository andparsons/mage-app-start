<?php

namespace Magento\NegotiableQuoteSharedCatalog\Test\Unit\Model\NegotiableQuote\Item;

/**
 * Unit test for Delete model.
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ItemRemove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemRemove;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemRepository;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete
     */
    private $itemDelete;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quoteItemRepository = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->itemRemove = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\ItemRemove::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemDelete = $objectManager->getObject(
            \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete::class,
            [
                'itemRemove' => $this->itemRemove,
                'quoteItemRepository' => $this->quoteItemRepository,
            ]
        );
    }

    /**
     * Test for deleteItems method.
     *
     * @return void
     */
    public function testDeleteItems()
    {
        $quoteId = 1;
        $quoteItemId = 2;
        $productId = 3;
        $sku = 'sku';
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductId', 'getOrigData', 'getItemId', 'getSku'])
            ->getMock();
        $quoteItem->expects($this->atLeastOnce())->method('getOrigData')->with('quote_id')->willReturn($quoteId);
        $quoteItem->expects($this->once())->method('getItemId')->willReturn($quoteItemId);
        $quoteItem->expects($this->atLeastOnce())->method('getProductId')->willReturn($productId);
        $quoteItem->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->quoteItemRepository->expects($this->once())->method('deleteById')->with($quoteId, $quoteItemId);
        $this->itemRemove->expects($this->once())->method('setNotificationRemove')->with($quoteId, $productId, [$sku]);

        $this->itemDelete->deleteItems([$quoteItem]);
    }
}
