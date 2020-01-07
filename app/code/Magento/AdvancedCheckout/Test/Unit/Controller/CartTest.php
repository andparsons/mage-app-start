<?php
namespace Magento\AdvancedCheckout\Test\Unit\Controller;

class CartTest extends \PHPUnit\Framework\TestCase
{
    public function testControllerImplementsProductViewInterface()
    {
        $this->assertInstanceOf(
            \Magento\Catalog\Controller\Product\View\ViewInterface::class,
            $this->createMock(\Magento\AdvancedCheckout\Controller\Cart::class)
        );
    }
}
