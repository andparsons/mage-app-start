<?php

namespace Magento\RequisitionList\Test\Unit\Block\Requisition\View\Items;

/**
 * Unit test for Magento\RequisitionList\Block\Requisition\View\Items\Grid.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Validation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validation;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\ItemSelector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemSelector;

    /**
     * @var \Magento\RequisitionList\Block\Requisition\View\Items\Grid
     */
    private $grid;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsite'])
            ->getMockForAbstractClass();
        $this->validation = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Validation::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->itemSelector = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionList\ItemSelector::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->grid = $objectManager->getObject(
            \Magento\RequisitionList\Block\Requisition\View\Items\Grid::class,
            [
                '_request' => $this->request,
                'storeManager' => $this->storeManager,
                'validation' => $this->validation,
                'itemSelector' => $this->itemSelector
            ]
        );
    }

    /**
     * Test for getRequisitionListItems method.
     *
     * @return void
     */
    public function testGetRequisitionListItems()
    {
        $requisitionListId = 1;
        $websiteId = 1;

        $this->request->expects($this->once())
            ->method('getParam')->with('requisition_id')->willReturn($requisitionListId);
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()->setMethods(['getId'])->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($websiteMock);
        $websiteMock->expects($this->atLeastOnce())->method('getId')->willReturn($websiteId);
        $this->itemSelector->expects($this->atLeastOnce())->method('selectAllItemsFromRequisitionList')
            ->with($requisitionListId, $websiteId)->willReturn([$requisitionListItem]);
        $this->validation->expects($this->once())->method('validate')->with($requisitionListItem)->willReturn([]);
        $this->assertEquals([$requisitionListItem], $this->grid->getRequisitionListItems());
        $this->assertEquals(0, $this->grid->getItemErrorCount());
    }

    /**
     * Test for getRequisitionListItems method with empty requisition list id.
     *
     * @return void
     */
    public function testGetRequisitionListItemsWithEmptyRequisitionListId()
    {
        $this->request->expects($this->once())->method('getParam')->with('requisition_id')->willReturn(null);
        $this->assertNull($this->grid->getRequisitionListItems());
    }

    /**
     * Test for getRequisitionListItems method with validation errors.
     *
     * @return void
     */
    public function testGetRequisitionListItemsWithValidationErrors()
    {
        $requisitionListId = 1;
        $websiteId = 1;

        $validationError = 'Item validation error';
        $this->request->expects($this->once())
            ->method('getParam')->with('requisition_id')->willReturn($requisitionListId);
        $requisitionListItems = [
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->setMethods(['getSku', 'setProduct', 'setNoProduct', 'setItemError', 'getItemError'])
                ->disableOriginalConstructor()->getMockForAbstractClass(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->setMethods(['getSku', 'setProduct', 'setNoProduct', 'setItemError', 'getItemError'])
                ->disableOriginalConstructor()->getMockForAbstractClass(),
            $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
                ->setMethods(['getSku', 'setProduct', 'setNoProduct', 'setItemError', 'getItemError'])
                ->disableOriginalConstructor()->getMockForAbstractClass()
        ];
        $this->itemSelector->expects($this->atLeastOnce())->method('selectAllItemsFromRequisitionList')
            ->with($requisitionListId, $websiteId)->willReturn($requisitionListItems);
        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()->setMethods(['getId'])->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($websiteMock);
        $websiteMock->expects($this->atLeastOnce())->method('getId')->willReturn($websiteId);
        $this->validation->expects($this->at(0))->method('validate')->with($requisitionListItems[0])->willReturn([]);
        $this->validation->expects($this->at(1))
            ->method('validate')->with($requisitionListItems[1])->willReturn([$validationError]);
        $this->validation->expects($this->at(2))->method('validate')->with($requisitionListItems[2])
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $requisitionListItems[0]->expects($this->atLeastOnce())->method('getId')->willReturn(10);
        $requisitionListItems[1]->expects($this->atLeastOnce())->method('getId')->willReturn(11);
        $requisitionListItems[2]->expects($this->atLeastOnce())->method('getId')->willReturn(12);
        $this->assertSame(
            [2 => $requisitionListItems[2], 1 => $requisitionListItems[1], 0 => $requisitionListItems[0]],
            $this->grid->getRequisitionListItems()
        );
        $this->assertEquals(2, $this->grid->getItemErrorCount());
        $this->assertEquals([], $this->grid->getItemErrors($requisitionListItems[0]));
        $this->assertEquals([$validationError], $this->grid->getItemErrors($requisitionListItems[1]));
        $this->assertEquals(
            [__('The SKU was not found in the catalog.')],
            $this->grid->getItemErrors($requisitionListItems[2])
        );
    }
}
