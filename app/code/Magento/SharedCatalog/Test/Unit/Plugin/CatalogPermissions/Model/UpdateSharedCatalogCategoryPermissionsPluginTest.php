<?php

namespace Magento\SharedCatalog\Test\Unit\Plugin\CatalogPermissions\Model;

/**
 * Unit test for Magento\SharedCatalog\Plugin\CatalogPermissions\Model\UpdateSharedCatalogCategoryPermissionsPlugin.
 */
class UpdateSharedCatalogCategoryPermissionsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\CatalogPermissionManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPermissionManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogConfig;

    /**
     * @var \Magento\SharedCatalog\Plugin\CatalogPermissions\Model\UpdateSharedCatalogCategoryPermissionsPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->catalogPermissionManagement = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\CatalogPermissionManagement::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogConfig = $this->getMockBuilder(\Magento\SharedCatalog\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\SharedCatalog\Plugin\CatalogPermissions\Model\UpdateSharedCatalogCategoryPermissionsPlugin::class,
            [
                'catalogPermissionManagement' => $this->catalogPermissionManagement,
                'sharedCatalogConfig' => $this->sharedCatalogConfig
            ]
        );
    }

    /**
     * Test afterSave method.
     *
     * @return void
     */
    public function testAfterSave()
    {
        $categoryId = 12;
        $customerGroup = 3;
        $websiteId = 1;

        $subject = $this->getMockBuilder(\Magento\CatalogPermissions\Model\Permission::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder(\Magento\CatalogPermissions\Model\Permission::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategoryId', 'getCustomerGroupId', 'getWebsiteId', 'getGrantCatalogCategoryView'])
            ->getMock();
        $result->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $result->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroup);
        $result->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $result->expects($this->once())->method('getGrantCatalogCategoryView')->willReturn(1);
        $this->sharedCatalogConfig->expects($this->once())
            ->method('isActive')
            ->with(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId)
            ->willReturn(true);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('updateSharedCatalogPermission')
            ->with($categoryId, $websiteId, $customerGroup, 1);

        $this->assertEquals($result, $this->plugin->afterSave($subject, $result));
    }

    /**
     * Test afterDelete method.
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $categoryId = 12;
        $customerGroup = 3;
        $websiteId = 1;
        $permission = \Magento\CatalogPermissions\Model\Permission::PERMISSION_DENY;

        $subject = $this->getMockBuilder(\Magento\CatalogPermissions\Model\Permission::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder(\Magento\CatalogPermissions\Model\Permission::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategoryId', 'getCustomerGroupId', 'getWebsiteId'])
            ->getMock();
        $result->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $result->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroup);
        $result->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->sharedCatalogConfig->expects($this->once())
            ->method('isActive')
            ->with(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteId)
            ->willReturn(true);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('updateSharedCatalogPermission')
            ->with($categoryId, $websiteId, $customerGroup, $permission);

        $this->assertEquals($result, $this->plugin->afterDelete($subject, $result));
    }
}
