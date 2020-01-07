<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Item;

/**
 * Class DeleteTest
 */
class DeleteTest extends ActionTest
{
    /**
     * @var string
     */
    protected $mockClass = 'Delete';

    /**
     * Prepare requisition list
     */
    protected function prepareRequisitionList()
    {
        $item =
            $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class);
        $item->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->requisitionList->expects($this->once())->method('getItems')->willReturn([$item]);
        $this->requisitionList->expects($this->any())->method('setUpdatedAt')->willReturnSelf();
        $this->requisitionListRepository->expects($this->once())->method('get')->willReturn($this->requisitionList);
    }
}
