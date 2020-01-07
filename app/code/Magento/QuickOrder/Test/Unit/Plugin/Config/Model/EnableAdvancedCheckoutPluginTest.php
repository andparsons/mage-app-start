<?php
namespace Magento\QuickOrder\Test\Unit\Plugin\Config\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

/**
 * Unit tests for EnableAdvancedCheckoutPlugin plugin.
 */
class EnableAdvancedCheckoutPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\QuickOrder\Plugin\Config\Model\EnableAdvancedCheckoutPlugin
     */
    private $enableAdvancedCheckoutPlugin;

    /**
     * @var \Magento\QuickOrder\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quickOrderConfigMock;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configResourceMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->quickOrderConfigMock = $this->getMockBuilder(\Magento\QuickOrder\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configResourceMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'saveConfig',
                'deleteConfig'
            ])
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getWebsite'
            ])
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->enableAdvancedCheckoutPlugin = $this->objectManagerHelper->getObject(
            \Magento\QuickOrder\Plugin\Config\Model\EnableAdvancedCheckoutPlugin::class,
            [
                'quickOrderConfig' => $this->quickOrderConfigMock,
                'configResource' => $this->configResourceMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Test for aroundSave() method when store scope applied for configuration.
     *
     * @return void
     */
    public function testAroundSaveForStoreScope()
    {
        $storeCode = 'store_code';
        $storeId = 1;

        $configMock = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getWebsite'
            ])
            ->getMock();
        $configMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeCode);
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCode',
                'getId'
            ])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->with($storeCode)
            ->willReturn($storeMock);
        $this->storeManagerMock->expects($this->never())->method('getWebsite');
        $storeMock->expects($this->atLeastOnce())->method('getCode')->willReturn($storeCode);
        $storeMock->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);
        $this->quickOrderConfigMock->expects($this->exactly(2))->method('isActive')->with('stores', $storeCode)
            ->willReturnOnConsecutiveCalls(false, true);
        $closure = function () use ($configMock) {
            return $configMock;
        };
        $this->configResourceMock->expects($this->once())->method('saveConfig')->with(
            'sales/product_sku/my_account_enable',
            \Magento\AdvancedCheckout\Model\Cart\Sku\Source\Settings::YES_VALUE,
            'stores',
            $storeId
        );

        $this->assertSame($configMock, $this->enableAdvancedCheckoutPlugin->aroundSave($configMock, $closure));
    }

    /**
     * Test for aroundSave() method when website scope applied for configuration.
     *
     * @return void
     */
    public function testAroundSaveForWebsiteScope()
    {
        $websiteCode = 'website_code';
        $websiteId = 1;

        $configMock = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getWebsite'
            ])
            ->getMock();
        $configMock->expects($this->atLeastOnce())->method('getStore')->willReturn(false);
        $configMock->expects($this->atLeastOnce())->method('getWebsite')->willReturn($websiteCode);
        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCode',
                'getId'
            ])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getWebsite')->with($websiteCode)
            ->willReturn($websiteMock);
        $websiteMock->expects($this->atLeastOnce())->method('getCode')->willReturn($websiteCode);
        $websiteMock->expects($this->atLeastOnce())->method('getId')->willReturn($websiteId);
        $this->quickOrderConfigMock->expects($this->exactly(2))->method('isActive')->with('websites', $websiteCode)
            ->willReturnOnConsecutiveCalls(false, true);
        $closure = function () use ($configMock) {
            return $configMock;
        };
        $this->configResourceMock->expects($this->once())->method('saveConfig')->with(
            'sales/product_sku/my_account_enable',
            \Magento\AdvancedCheckout\Model\Cart\Sku\Source\Settings::YES_VALUE,
            'websites',
            $websiteId
        );

        $this->assertSame($configMock, $this->enableAdvancedCheckoutPlugin->aroundSave($configMock, $closure));
    }

    /**
     * @return void
     */
    public function testAroundSave()
    {
        $scopeCode = null;
        $scopeId = 0;
        $websiteId = 1;

        $configMock = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getWebsite'
            ])
            ->getMock();
        $configMock->expects($this->atLeastOnce())->method('getStore')->willReturn(false);
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('getWebsite');
        $this->quickOrderConfigMock->expects($this->exactly(2))->method('isActive')->with(
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeCode
        )
            ->willReturnOnConsecutiveCalls(false, true);
        $closure = function () use ($configMock) {
            return $configMock;
        };
        $this->configResourceMock->expects($this->once())->method('saveConfig')->with(
            'sales/product_sku/my_account_enable',
            \Magento\AdvancedCheckout\Model\Cart\Sku\Source\Settings::YES_VALUE,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId
        );

        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$websiteMock]);
        $websiteMock->expects($this->atLeastOnce())->method('getId')->willReturn($websiteId);
        $this->configResourceMock->expects($this->once())->method('deleteConfig')->with(
            'sales/product_sku/my_account_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        $this->assertSame($configMock, $this->enableAdvancedCheckoutPlugin->aroundSave($configMock, $closure));
    }
}
