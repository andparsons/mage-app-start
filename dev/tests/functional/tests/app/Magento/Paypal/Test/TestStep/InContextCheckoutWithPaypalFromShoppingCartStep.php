<?php

namespace Magento\Paypal\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Checkout with PayPal from Shopping Cart.
 */
class InContextCheckoutWithPaypalFromShoppingCartStep implements TestStepInterface
{
    /**
     * Shopping Cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     */
    public function __construct(
        CheckoutCart $checkoutCart
    ) {
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Checkout with PayPal from Shopping Cart.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->inContextPaypalCheckout();
    }
}
