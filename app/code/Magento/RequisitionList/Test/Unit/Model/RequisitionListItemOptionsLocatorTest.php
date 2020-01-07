<?php

namespace Magento\RequisitionList\Test\Unit\Model;

/**
 * Unit test for RequisitionListItemOptionsLocator model.
 */
class RequisitionListItemOptionsLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemOptionsFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListOptionsItemFactory;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemProduct;

    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsManagement;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemOptionsLocator
     */
    private $requisitionListItemOptionsLocator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requisitionListOptionsItemFactory = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItemOptionsFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItemProduct::class)
            ->disableOriginalConstructor()->getMock();
        $this->optionsManagement = $this->getMockBuilder(\Magento\RequisitionList\Model\OptionsManagement::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requisitionListItemOptionsLocator = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListItemOptionsLocator::class,
            [
                'requisitionListOptionsItemFactory' => $this->requisitionListOptionsItemFactory,
                'requisitionListItemProduct' => $this->requisitionListItemProduct,
                'optionsManagement' => $this->optionsManagement,
            ]
        );
    }

    /**
     * Test for getOptions method.
     *
     * @return void
     */
    public function testGetOptions()
    {
        $itemId = 1;
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getId')->willReturn($itemId);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->requisitionListItemProduct->expects($this->once())
            ->method('getProduct')->with($item)->willReturn($product);
        $option = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Option::class)
            ->disableOriginalConstructor()->getMock();
        $this->optionsManagement->expects($this->once())->method('getOptions')->with($item)->willReturn([$option]);
        $optionItem = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->requisitionListOptionsItemFactory->expects($this->once())->method('create')->willReturn($optionItem);
        $optionItem->expects($this->atLeastOnce())->method('setData')
            ->withConsecutive(['product', $product], ['options', [$option]])->willReturnSelf();
        $this->assertEquals($optionItem, $this->requisitionListItemOptionsLocator->getOptions($item));
    }
}
