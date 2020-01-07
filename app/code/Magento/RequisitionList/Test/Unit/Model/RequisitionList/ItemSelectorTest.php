<?php
namespace Magento\RequisitionList\Test\Unit\Model\RequisitionList;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\RequisitionList\Model\RequisitionList\Items as ItemsRepository;
use Magento\RequisitionList\Model\RequisitionListItemProduct;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * Unit tests for Requisition List ItemsSelector model.
 */
class ItemSelectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\ItemSelector
     */
    private $itemSelector;

    /**
     * @var ItemsRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemRepositoryMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemProductMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->requisitionListItemRepositoryMock = $this->getMockBuilder(ItemsRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemProductMock = $this->getMockBuilder(RequisitionListItemProduct::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->itemSelector = $this->objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionList\ItemSelector::class,
            [
                'requisitionListItemRepository' => $this->requisitionListItemRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'requisitionListItemProduct' => $this->requisitionListItemProductMock
            ]
        );
    }

    /**
     * Test for selectAllItemsFromRequisitionList() method.
     *
     * @return void
     */
    public function testSelectAllItemsFromRequisitionList()
    {
        $requisitionListId = 1;
        $websiteId = 1;
        $productSku = 'SKU01';

        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')
            ->with(RequisitionListItemInterface::REQUISITION_LIST_ID, $requisitionListId)
            ->wilLReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListItemRepositoryMock->expects($this->once())->method('getList')->willReturn($searchResults);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $items = [$requisitionListItem];
        $searchResults->expects($this->once())->method('getItems')->willReturn($items);
        $this->requisitionListItemProductMock->expects($this->once())
            ->method('extract')->with([$requisitionListItem], $websiteId, true)->willReturn([$productSku => $product]);
        $requisitionListItem->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $this->requisitionListItemProductMock->expects($this->once())->method('setProduct')
            ->with($requisitionListItem, $product)->willReturnSelf();
        $this->requisitionListItemProductMock->expects($this->once())->method('setIsProductAttached')
            ->with($requisitionListItem, true)->willReturnSelf();

        $this->assertEquals(
            [$requisitionListItem],
            $this->itemSelector->selectAllItemsFromRequisitionList($requisitionListId, $websiteId)
        );
    }

    /**
     * Test for selectItemsFromRequisitionList() method.
     *
     * @return void
     */
    public function testSelectItemsFromRequisitionList()
    {
        $requisitionListId = 1;
        $websiteId = 1;
        $productSku = 'SKU01';
        $itemIds = [1];

        $this->searchCriteriaBuilderMock->expects($this->exactly(2))->method('addFilter')
            ->withConsecutive(
                [RequisitionListItemInterface::REQUISITION_LIST_ID, $requisitionListId],
                [RequisitionListItemInterface::REQUISITION_LIST_ITEM_ID, $itemIds, 'in']
            )
            ->wilLReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListItemRepositoryMock->expects($this->once())->method('getList')->willReturn($searchResults);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $items = [$requisitionListItem];
        $searchResults->expects($this->once())->method('getItems')->willReturn($items);
        $this->requisitionListItemProductMock->expects($this->once())
            ->method('extract')->with([$requisitionListItem], $websiteId, false)->willReturn([$productSku => $product]);
        $requisitionListItem->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $this->requisitionListItemProductMock->expects($this->once())->method('setProduct')
            ->with($requisitionListItem, $product)->willReturnSelf();
        $this->requisitionListItemProductMock->expects($this->once())->method('setIsProductAttached')
            ->with($requisitionListItem, true)->willReturnSelf();

        $this->assertEquals(
            [$requisitionListItem],
            $this->itemSelector->selectItemsFromRequisitionList($requisitionListId, $itemIds, $websiteId)
        );
    }
}
