<?php
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

/**
 * Web Api order create in single stock mode virtual product tests.
 */
class OrderCreateSingleStockModeVirtualProductTest extends OrderPlacementBase
{
    /**
     * Create order with virtual product - registered customer, single stock mode, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/915538/scenarios/1855115
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Create order with virtual product - registered customer, single stock mode, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @return void
     */
    public function testCustomerPlaceOrderAdditionalWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $websiteCode = 'eu_website';
        $this->assignCustomerToCustomWebsite('customer@example.com', $websiteCode);
        $this->assignProductsToWebsite(['virtual-product'], $websiteCode);
        $this->setStoreView('store_for_eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Create order with virtual product - guest customer, single stock mode, default website.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @return void
     */
    public function testGuestPlaceOrderDefaultWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Create order with virtual product - guest customer, single stock mode, additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @return void
     */
    public function testGuestPlaceOrderAdditonalWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $websiteCode = 'eu_website';
        $this->assignProductsToWebsite(['virtual-product'], $websiteCode);
        $this->setStoreView('store_for_eu_website');
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Verify created order is correct.
     *
     * @param int $orderId
     * @return void
     */
    private function verifyCreatedOrder(int $orderId): void
    {
        $order = $this->getOrder($orderId);
        $this->assertGreaterThan(0, $order['increment_id']);
        $this->assertEquals('customer@example.com', $order['customer_email']);
        $this->assertEquals('virtual-product', $order['items'][0]['sku']);
        $this->assertEquals('virtual', $order['items'][0]['product_type']);
        $this->assertEquals(10, $order['items'][0]['price']);
        $this->assertEquals(1, $order['items'][0]['qty_ordered']);
    }
}
