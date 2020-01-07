<?php

namespace Magento\RequisitionList\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface adding products from requisition lists to cart.
 *
 * @api
 * @since 100.0.0
 */
interface AddToCartProcessorInterface
{
    /**
     * Add a product from a requisition list to cart.
     *
     * @param CartInterface $cart
     * @param CartItemInterface $cartItem
     * @return void
     */
    public function execute(CartInterface $cart, CartItemInterface $cartItem);
}
