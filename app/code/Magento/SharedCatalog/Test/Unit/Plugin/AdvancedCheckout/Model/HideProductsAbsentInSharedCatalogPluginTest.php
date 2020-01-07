<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Unit\Plugin\AdvancedCheckout\Model;

/**
 * Unit test for \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\HideProductsAbsentInSharedCatalogPlugin.
 *
 * @covers \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\HideProductsAbsentInSharedCatalogPlugin
 */
class HideProductsAbsentInSharedCatalogPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Model\Customer\AvailableProducts|\PHPUnit_Framework_MockObject_MockObject
     */
    private $availableProducts;

    /**
     * @var \Magento\AdvancedCheckout\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cart;

    /**
     * @var \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\HideProductsAbsentInSharedCatalogPlugin
     */
    private $cartPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(\Magento\SharedCatalog\Api\StatusInfoInterface::class)
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->availableProducts = $this->getMockBuilder(\Magento\SharedCatalog\Model\Customer\AvailableProducts::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cart = $this->getMockBuilder(\Magento\AdvancedCheckout\Model\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cartPlugin = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\HideProductsAbsentInSharedCatalogPlugin::class,
            [
                'config' => $this->config,
                'storeManager' => $this->storeManager,
                'availableProducts' => $this->availableProducts,
            ]
        );
    }

    /**
     * Test for afterCheckItem().
     *
     * @param bool $isActive
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $call
     * @param array $item
     * @param array $result
     * @return void
     * @dataProvider afterCheckItemDataProvider
     */
    public function testAfterCheckItem(
        bool $isActive,
        \PHPUnit\Framework\MockObject\Matcher\Invocation $call,
        array $item,
        array $result
    ): void {
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->getMock();
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->willReturn($website);
        $this->config->expects($this->atLeastOnce())
            ->method('isActive')
            ->willReturn($isActive);

        $customerGroupId = 99;
        $customer = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($call)
            ->method('getId')
            ->willReturn(666);
        $customer->expects($call)
            ->method('getGroupId')
            ->willReturn($customerGroupId);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($call)
            ->method('getCustomer')
            ->willReturn($customer);
        $this->cart->expects($call)
            ->method('getActualQuote')
            ->willReturn($quote);
        $this->availableProducts->expects($call)
            ->method('isProductAvailable')
            ->with($customerGroupId, $item['sku'])
            ->willReturn((bool) \array_intersect(['test_sku_1', 'test_sku_2'], [$item['sku']]));

        $this->assertEquals($result, $this->cartPlugin->afterCheckItem($this->cart, $item));
    }

    /**
     * Data provider for afterCheckItem() test.
     *
     * @return array
     */
    public function afterCheckItemDataProvider(): array
    {
        return [
            [
                false,
                $this->never(),
                ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS, 'sku' => 'test_sku_1'],
                ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS, 'sku' => 'test_sku_1']
            ],
            [
                true,
                $this->atLeastOnce(),
                ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS, 'sku' => 'test_sku_1'],
                ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS, 'sku' => 'test_sku_1']
            ],
            [
                true,
                $this->atLeastOnce(),
                ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS, 'sku' => 'test_sku_3'],
                ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU, 'sku' => 'test_sku_3']
            ],
        ];
    }
}
