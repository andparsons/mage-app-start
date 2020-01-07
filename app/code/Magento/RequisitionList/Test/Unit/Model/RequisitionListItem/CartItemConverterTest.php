<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\RequisitionList\Model\RequisitionListItem\CartItemConverter;

/**
 * Unit test for CartItemConverter.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemFactoryMock;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productOptionFactoryMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemProcessorMock;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemProduct;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var CartItemConverter
     */
    private $cartItemConverter;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->cartItemFactoryMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productOptionFactoryMock =
            $this->getMockBuilder(\Magento\Quote\Api\Data\ProductOptionInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItemProcessorMock =
            $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemProduct = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItemProduct::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->cartItemConverter = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\CartItemConverter::class,
            [
                'cartItemFactory' => $this->cartItemFactoryMock,
                'productOptionFactory' => $this->productOptionFactoryMock,
                'cartItemProcessor' => $this->cartItemProcessorMock,
                'requisitionListItemProduct' => $this->requisitionListItemProduct,
                'serializer' => $this->serializer,
            ]
        );
    }

    /**
     * Test convert().
     *
     * @return void
     */
    public function testConvert()
    {
        $itemMock = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequestMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->atLeastOnce())
            ->method('getCustomOptions')
            ->willReturn([$buyRequestMock]);
        $this->requisitionListItemProduct->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($productMock);
        $cartItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cartItemMock->expects($this->atLeastOnce())
            ->method('getOptionByCode')
            ->willReturn($buyRequestMock);
        $this->cartItemFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($cartItemMock);
        $productOptionMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\ProductOption::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productOptionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($productOptionMock);
        $this->serializer->expects($this->atLeastOnce())->method('serialize')->willReturn('serialized');
        $cartItem = $this->cartItemConverter->convert($itemMock);

        $this->assertInstanceOf(\Magento\Quote\Api\Data\CartItemInterface::class, $cartItem);
    }
}
