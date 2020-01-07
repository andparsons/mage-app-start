<?php
namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for SaveHandler.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsBuilder;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListManagement;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Locator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemLocator;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListProduct;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\SaveHandler
     */
    private $saveHandler;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requisitionListRepository = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->optionsBuilder = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Options\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListManagement = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\RequisitionListManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemLocator = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Locator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListProduct = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListProduct::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->saveHandler = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\SaveHandler::class,
            [
                'requisitionListRepository' => $this->requisitionListRepository,
                'optionsBuilder' => $this->optionsBuilder,
                'requisitionListManagement' => $this->requisitionListManagement,
                'requisitionListItemLocator' => $this->requisitionListItemLocator,
                'requisitionListProduct' => $this->requisitionListProduct,
            ]
        );
    }

    /**
     * Test saveItem method.
     *
     * @param int|null $itemId
     * @param string $productName
     * @param int $count
     * @param string $rlName
     * @param \Magento\Framework\Phrase $expectedResult
     * @return void
     * @dataProvider saveItemDataProvider
     */
    public function testSaveItem($itemId, $productName, $count, $rlName, \Magento\Framework\Phrase $expectedResult)
    {
        $listId = 1;
        $requisitionList = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $item->expects($this->atLeastOnce())->method('setQty')->willReturnSelf();
        $item->expects($this->atLeastOnce())->method('setOptions')->willReturnSelf();
        $item->expects($this->atLeastOnce())->method('setSku')->willReturnSelf();
        $item->expects($this->atLeastOnce())->method('getId')->willReturn($itemId);
        $requisitionList->expects($this->atLeastOnce())->method('getItems')->willReturn([1 => $item]);
        $requisitionList->expects($this->exactly($count))->method('getName')->willReturn($rlName);
        $this->requisitionListRepository->expects($this->atLeastOnce())->method('get')->with($listId)
            ->willReturn($requisitionList);
        $this->optionsBuilder->expects($this->atLeastOnce())->method('build')->willReturn([]);
        $productData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'getSku'])
            ->getMock();
        $productData->expects($this->atLeastOnce())->method('getOptions')->with('qty')->willReturn(1);
        $productData->expects($this->atLeastOnce())->method('getSku')->willReturn('sku');
        $this->requisitionListItemLocator->expects($this->atLeastOnce())->method('getItem')->with($itemId)
            ->willReturn($item);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getName')->willReturn($productName);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $this->requisitionListManagement->expects($this->atLeastOnce())->method('setItemsToList');
        $this->requisitionListRepository->expects($this->atLeastOnce())->method('save')->willReturn($requisitionList);
        $this->assertEquals($expectedResult, $this->saveHandler->saveItem($productData, [], $itemId, $listId));
    }

    /**
     * Data provider for saveItem method.
     *
     * @return array
     */
    public function saveItemDataProvider()
    {
        return [
            [
                1,
                'product name',
                0,
                '',
                new \Magento\Framework\Phrase('%1 has been updated in your requisition list.', ['product name'])
            ],
            [
                null,
                'product name',
                1,
                'requisition list name',
                new \Magento\Framework\Phrase(
                    'Product %1 has been added to the requisition list %2.',
                    ['product name', 'requisition list name']
                )
            ],
        ];
    }
}
