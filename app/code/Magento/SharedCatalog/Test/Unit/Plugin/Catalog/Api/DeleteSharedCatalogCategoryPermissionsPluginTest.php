<?php
namespace Magento\SharedCatalog\Test\Unit\Plugin\Catalog\Api;

use Magento\SharedCatalog\Plugin\Catalog\Api\DeleteSharedCatalogCategoryPermissionsPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for DeleteSharedCatalogCategoryPermissionsPlugin.
 */
class DeleteSharedCatalogCategoryPermissionsPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\Permission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogPermissionResource;

    /**
     * @var DeleteSharedCatalogCategoryPermissionsPlugin
     */
    private $deleteSharedCatalogCategoryPermissionsPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogPermissionResource = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\Permission::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->deleteSharedCatalogCategoryPermissionsPlugin = $objectManagerHelper->getObject(
            DeleteSharedCatalogCategoryPermissionsPlugin::class,
            [
                'sharedCatalogPermissionResource' => $this->sharedCatalogPermissionResource,
            ]
        );
    }

    /**
     * Test for afterDelete().
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $categoryId = 1;
        $categoryRepository = $this->getMockBuilder(\Magento\Catalog\Api\CategoryRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $category = $this->getMockBuilder(\Magento\Catalog\Api\Data\CategoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $category->expects($this->atLeastOnce())->method('getId')->willReturn($categoryId);
        $this->sharedCatalogPermissionResource->expects($this->atLeastOnce())->method('deleteItems')->with($categoryId);

        $this->assertTrue(
            $this->deleteSharedCatalogCategoryPermissionsPlugin->afterDelete($categoryRepository, true, $category)
        );
    }
}
