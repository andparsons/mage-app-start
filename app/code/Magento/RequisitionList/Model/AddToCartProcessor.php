<?php

namespace Magento\RequisitionList\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Add a product from a requisition list to cart.
 */
class AddToCartProcessor implements AddToCartProcessorInterface
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor
     */
    private $cartItemOptionProcessor;

    /**
     * @param \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor $cartItemOptionProcessor
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor $cartItemOptionProcessor
    ) {
        $this->cartItemOptionProcessor = $cartItemOptionProcessor;
    }

    /**
     * Add a product from a requisition list to cart.
     *
     * @param CartInterface $cart
     * @param CartItemInterface $cartItem
     * @return void
     */
    public function execute(CartInterface $cart, CartItemInterface $cartItem)
    {
        $product = $cartItem->getData('product');
        $productOptions = $this->cartItemOptionProcessor->getBuyRequest($product->getTypeId(), $cartItem);
        $cart->addProduct($product, $productOptions);
    }
}
