<?php

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionList;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\RequisitionList\Items;
use Magento\RequisitionList\Model\RequisitionList\ItemsLoadHandler;

/**
 * Class ItemsLoadHandlerTest
 */
class ItemsLoadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Items
     */
    private $requisitionListItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ItemsLoadHandler
     */
    private $itemsLoadHandler;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->requisitionListItemRepository =
            $this->createMock(\Magento\RequisitionList\Model\RequisitionList\Items::class);
        $this->searchCriteriaBuilder =
            $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemsLoadHandler = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionList\ItemsLoadHandler::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'requisitionListItemRepository' => $this->requisitionListItemRepository
            ]
        );
    }

    /**
     * Test for method load
     */
    public function testLoad()
    {
        $rlId = 1;
        $requisitionList = $this->getMockForAbstractClass(
            \Magento\RequisitionList\Api\Data\RequisitionListInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'setItems']
        );
        $requisitionList->expects($this->once())->method('getId')->willReturn($rlId);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(RequisitionListItemInterface::REQUISITION_LIST_ID)
            ->willReturnSelf();
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $this->requisitionListItemRepository->expects($this->once())
            ->method('getList')
            ->willReturn($searchResults);
        $item =
            $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class);
        $items = [$item];
        $searchResults->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $requisitionList->expects($this->once())->method('setItems')->with($items)->willReturnSelf();
        $this->assertEquals($requisitionList, $this->itemsLoadHandler->load($requisitionList));
    }
}
