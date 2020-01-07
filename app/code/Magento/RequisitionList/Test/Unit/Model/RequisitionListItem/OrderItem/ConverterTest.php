<?php

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem\OrderItem;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;

/**
 * Unit test for Converter.
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsBuilder;

    /**
     * @var RequisitionListItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\OrderItem\Converter
     */
    private $converter;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->optionsBuilder = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Options\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemFactory = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->converter = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\OrderItem\Converter::class,
            [
                'optionsBuilder' => $this->optionsBuilder,
                'requisitionListItemFactory' => $this->requisitionListItemFactory,
            ]
        );
    }

    /**
     * Test for convert() method.
     *
     * @return void
     */
    public function testConvert()
    {
        $sku = 'sku';
        $productOptions = ['info_buyRequest' => ['options']];
        $orderItem = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductOptions'])
            ->getMockForAbstractClass();
        $orderItem->expects($this->atLeastOnce())->method('getProductOptions')->willReturn($productOptions);
        $orderItem->expects($this->atLeastOnce())->method('getQtyOrdered')->willReturn(1);
        $this->optionsBuilder->expects($this->atLeastOnce())->method('build')
            ->with($productOptions['info_buyRequest'], 0)->willReturn([]);
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requisitionListItem->expects($this->atLeastOnce())->method('setQty')->willReturnSelf();
        $requisitionListItem->expects($this->atLeastOnce())->method('setOptions')->willReturnSelf();
        $requisitionListItem->expects($this->atLeastOnce())->method('setSku')->willReturnSelf();
        $this->requisitionListItemFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($requisitionListItem);

        $this->assertEquals($requisitionListItem, $this->converter->convert($orderItem, $sku));
    }
}
