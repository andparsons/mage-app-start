<?php
namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Locator.
 */
class LocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemRepository;

    /**
     * @var RequisitionListItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Locator
     */
    private $locator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requisitionListItemRepository = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionList\Items::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemFactory = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->locator = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\Locator::class,
            [
                'requisitionListItemRepository' => $this->requisitionListItemRepository,
                'requisitionListItemFactory' => $this->requisitionListItemFactory,
            ]
        );
    }

    /**
     * Test for getItem().
     *
     * @return void
     */
    public function testGetItem()
    {
        $itemId = 1;
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemRepository->expects($this->atLeastOnce())->method('get')->with($itemId)
            ->willReturn($requisitionListItem);
        $this->requisitionListItemFactory->expects($this->never())->method('create');

        $this->assertInstanceOf(
            \Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class,
            $this->locator->getItem($itemId)
        );
    }

    /**
     * Test for getItem() with empty item id.
     *
     * @return void
     */
    public function testGetItemWithEmptyItemId()
    {
        $itemId = null;
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($requisitionListItem);
        $this->requisitionListItemRepository->expects($this->never())->method('get')->with($itemId);

        $this->assertInstanceOf(
            \Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class,
            $this->locator->getItem($itemId)
        );
    }
}
