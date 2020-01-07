<?php

namespace Magento\RequisitionList\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for AddToCartProcessor.
 */
class AddToCartProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemOptionProcessor;

    /**
     * @var \Magento\RequisitionList\Model\AddToCartProcessor
     */
    private $addToCartProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->cartItemOptionProcessor = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->addToCartProcessor = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\AddToCartProcessor::class,
            [
                'cartItemOptionProcessor' => $this->cartItemOptionProcessor,
            ]
        );
    }

    /**
     * Test for execute().
     *
     * @return void
     */
    public function testExecute()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeId')->willReturn('simple');
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $cartItem->expects($this->atLeastOnce())->method('getData')->with('product')->willReturn($product);
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItemOptionProcessor->expects($this->atLeastOnce())->method('getBuyRequest')->willReturn($buyRequest);
        $cart = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addProduct'])
            ->getMockForAbstractClass();
        $cart->expects($this->atLeastOnce())->method('addProduct')->with($product, $buyRequest);
        $this->addToCartProcessor->execute($cart, $cartItem);
    }
}
