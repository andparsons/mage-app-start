<?php
namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for Shared Catalog Config model.
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

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
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testIsActive()
    {
        $result = false;
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $scopeCode = null;

        $this->scopeConfigMock->expects($this->once())->method('isSetFlag')->with(
            'btob/website_configuration/sharedcatalog_active',
            $scopeType,
            $scopeCode
        )->willReturn($result);

        $this->assertEquals($result, $this->config->isActive($scopeType, $scopeCode));
    }

    /**
     * @return void
     */
    public function testGetActiveSharedCatalogStoreIds()
    {
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $scopeCode = 'default';
        $storeId = 1;
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStores'])
            ->getMockForAbstractClass();
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->with(true)->willReturn([$website]);
        $website->expects($this->once())->method('getCode')->willReturn($scopeCode);
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('btob/website_configuration/sharedcatalog_active', $scopeType, $scopeCode)
            ->willReturn(true);
        $website->expects($this->once())->method('getStores')->willReturn([$store]);
        $store->expects($this->once())->method('getId')->willReturn($storeId);

        $this->assertSame([$storeId], $this->config->getActiveSharedCatalogStoreIds());
    }
}
