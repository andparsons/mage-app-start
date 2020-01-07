<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Unit\Plugin\Catalog\Model\ResourceModel;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\SharedCatalog\Model\State as SharedCatalogState;
use Magento\SharedCatalog\Plugin\Catalog\Model\ResourceModel\DenyPermissionsForNewCategory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see \Magento\SharedCatalog\Plugin\Catalog\Model\ResourceModel\DenyPermissionsForNewCategory
 */
class DenyPermissionsForNewCategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CatalogPermissionManagement|MockObject
     */
    private $catalogPermissionManagement;

    /**
     * @var SharedCatalogState|MockObject
     */
    private $sharedCatalogState;

    /**
     * @var DenyPermissionsForNewCategory
     */
    private $plugin;

    /**
     * @var CategoryResource|MockObject
     */
    private $subject;

    /**
     * @var CategoryModel|MockObject
     */
    private $category;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->catalogPermissionManagement = $this->createMock(CatalogPermissionManagement::class);
        $this->sharedCatalogState = $this->createMock(SharedCatalogState::class);
        $this->subject = $this->createMock(CategoryResource::class);
        $this->category = $this->createPartialMock(
            CategoryModel::class,
            ['getPermissions', 'getId', 'isObjectNew']
        );

        $objectManager = new ObjectManagerHelper($this);
        $this->plugin = $objectManager->getObject(
            DenyPermissionsForNewCategory::class,
            [
                'catalogPermissionManagement' => $this->catalogPermissionManagement,
                'sharedCatalogState' => $this->sharedCatalogState,
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
        $categoryId = 1;
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(true);
        $this->sharedCatalogState->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->category->expects($this->once())->method('getId')->willReturn($categoryId);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('setDenyPermissionsForCategory')
            ->with($categoryId);

        $this->plugin->afterSave($this->subject, $this->subject, $this->category);
    }

    /**
     * Test afterSave method when config is disabled.
     *
     * @return void
     */
    public function testAfterSaveWhenDisabledInConfig()
    {
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(true);
        $this->sharedCatalogState->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->catalogPermissionManagement->expects($this->never())->method('setDenyPermissionsForCategory');

        $this->plugin->afterSave($this->subject, $this->subject, $this->category);
    }

    /**
     * Test afterSave method when category is not new.
     *
     * @return void
     */
    public function testAfterSaveWhenCategoryIsNotNew(): void
    {
        $this->category->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->sharedCatalogState->expects($this->never())->method('isEnabled');
        $this->category->expects($this->never())->method('getPermissions');
        $this->category->expects($this->never())->method('getId');
        $this->catalogPermissionManagement->expects($this->never())->method('setDenyPermissionsForCategory');

        $this->plugin->afterSave($this->subject, $this->subject, $this->category);
    }
}
