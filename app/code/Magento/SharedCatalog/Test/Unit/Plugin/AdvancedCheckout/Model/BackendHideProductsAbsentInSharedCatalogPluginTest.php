<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Unit\Plugin\AdvancedCheckout\Model;

/**
 * Unit test for \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\BackendHideProductsAbsentInSharedCatalogPlugin.
 *
 * @covers \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\BackendHideProductsAbsentInSharedCatalogPlugin
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendHideProductsAbsentInSharedCatalogPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionQuoteMock;

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
    private $cartMock;

    /**
     * @var \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\BackendHideProductsAbsentInSharedCatalogPlugin
     */
    private $cartPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(\Magento\SharedCatalog\Api\StatusInfoInterface::class)
            ->getMock();
        $this->sessionQuoteMock = $this->getMockBuilder(\Magento\Backend\Model\Session\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->availableProducts = $this->getMockBuilder(\Magento\SharedCatalog\Model\Customer\AvailableProducts::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartMock = $this->getMockBuilder(\Magento\AdvancedCheckout\Model\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cartPlugin = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\AdvancedCheckout\Model\BackendHideProductsAbsentInSharedCatalogPlugin::class,
            [
                'config' => $this->configMock,
                'sessionQuote' => $this->sessionQuoteMock,
                'storeManager' => $this->storeManager,
                'availableProducts' => $this->availableProducts,
            ]
        );
    }

    /**
     * Test for afterCheckItem() method.
     *
     * @return void
     */
    public function testAfterCheckItem(): void
    {
        $groupId = 1;
        $customerId = 1;
        $item = ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS, 'sku' => 'test_sku_3'];
        $result = ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU, 'sku' => 'test_sku_3'];

        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->getMock();
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->willReturn($website);
        $this->configMock->expects($this->atLeastOnce())
            ->method('isActive')
            ->willReturn(true);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionQuoteMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);
        $this->availableProducts->expects($this->once())
            ->method('isProductAvailable')
            ->with($groupId, $item['sku'])
            ->willReturn(false);

        $this->assertEquals($result, $this->cartPlugin->afterCheckItem($this->cartMock, $item));
    }

    /**
     * Test for afterCheckItem() method when there is no customer ID in session.
     *
     * @return void
     */
    public function testAfterCheckItemIfNoCustomerInSession(): void
    {
        $groupId = \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID;
        $item = ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS, 'sku' => 'test_sku_3'];
        $result = ['code' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU, 'sku' => 'test_sku_3'];

        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->getMock();
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getWebsite')
            ->willReturn($website);
        $this->configMock->expects($this->atLeastOnce())
            ->method('isActive')
            ->willReturn(true);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionQuoteMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);
        $this->availableProducts->expects($this->once())
            ->method('isProductAvailable')
            ->with($groupId, $item['sku'])
            ->willReturn(false);

        $this->assertEquals($result, $this->cartPlugin->afterCheckItem($this->cartMock, $item));
    }
}
