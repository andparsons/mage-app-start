<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Item;

/**
 * Class UpdateTest
 */
class UpdateTest extends ActionTest
{
    /**
     * @var string
     */
    protected $mockClass = 'Update';

    /**
     * Prepare requisition list
     */
    protected function prepareRequisitionList()
    {
        $item =
            $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class);
        $item->expects($this->any())->method('getSku')->willReturn('sku');
        $item->expects($this->any())->method('setQty')->willReturnSelf();
        $this->requisitionList->expects($this->once())->method('getItems')->willReturn([$item]);
        $this->requisitionList->expects($this->any())->method('setUpdatedAt')->willReturnSelf();
        $this->requisitionListRepository->expects($this->once())->method('get')->willReturn($this->requisitionList);
    }
}
