<?php

namespace Magento\GiftCardRequisitionList\Test\Unit\Model;

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
     * @var \Magento\GiftCardRequisitionList\Model\AddToCartProcessor
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
            \Magento\GiftCardRequisitionList\Model\AddToCartProcessor::class,
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
            ->setMethods(['getTypeId', 'getAllowOpenAmount'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeId')->willReturn('giftcard');
        $product->expects($this->atLeastOnce())->method('getAllowOpenAmount')->willReturn(false);
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $cartItem->expects($this->atLeastOnce())->method('getData')->with('product')->willReturn($product);
        $productOptions = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getGiftcardAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $productOptions->expects($this->never())->method('getGiftcardAmount');
        $this->cartItemOptionProcessor->expects($this->atLeastOnce())->method('getBuyRequest')
            ->willReturn($productOptions);
        $cart = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addProduct'])
            ->getMockForAbstractClass();
        $cart->expects($this->atLeastOnce())->method('addProduct')->with($product, $productOptions);
        $this->addToCartProcessor->execute($cart, $cartItem);
    }

    /**
     * Test for execute() for gift cards with allowed open amount.
     *
     * @return void
     */
    public function testExecuteWithAllowedOpenAmount()
    {
        $giftCardAmount = 10;
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getAllowOpenAmount'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeId')->willReturn('giftcard');
        $product->expects($this->atLeastOnce())->method('getAllowOpenAmount')->willReturn(true);
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $cartItem->expects($this->atLeastOnce())->method('getData')->with('product')->willReturn($product);
        $productOptions = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGiftcardAmount', 'setGiftcardAmount', 'setCustomGiftcardAmount'])
            ->getMock();
        $productOptions->expects($this->atLeastOnce())->method('getGiftcardAmount')->willReturn($giftCardAmount);
        $productOptions->expects($this->atLeastOnce())->method('setCustomGiftcardAmount')->with($giftCardAmount)
            ->willReturnSelf();
        $productOptions->expects($this->atLeastOnce())->method('setGiftcardAmount')->with(null)->willReturnSelf();
        $this->cartItemOptionProcessor->expects($this->atLeastOnce())->method('getBuyRequest')
            ->willReturn($productOptions);
        $cart = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addProduct'])
            ->getMockForAbstractClass();
        $cart->expects($this->atLeastOnce())->method('addProduct')->with($product, $productOptions);
        $this->addToCartProcessor->execute($cart, $cartItem);
    }
}
