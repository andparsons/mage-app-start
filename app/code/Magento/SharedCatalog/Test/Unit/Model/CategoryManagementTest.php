<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category\StoreCategories;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\SharedCatalog\Model\CategoryManagement;
use Magento\SharedCatalog\Model\SharedCatalogAssignment;
use Magento\SharedCatalog\Model\SharedCatalogInvalidation;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * CategoryManagement unit test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SharedCatalogInvalidation|MockObject
     */
    private $sharedCatalogInvalidation;

    /**
     * @var CatalogPermissionManagement|MockObject
     */
    private $catalogPermissionManagement;

    /**
     * @var SharedCatalogAssignment|MockObject
     */
    private $sharedCatalogAssignment;

    /**
     * @var StoreCategories|MockObject
     */
    private $storeCategories;

    /**
     * @var CategoryManagement
     */
    private $categoryManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogInvalidation = $this->createMock(SharedCatalogInvalidation::class);
        $this->catalogPermissionManagement = $this->createMock(CatalogPermissionManagement::class);
        $this->sharedCatalogAssignment = $this->createMock(SharedCatalogAssignment::class);
        $this->storeCategories = $this->createMock(StoreCategories::class);

        $objectManager = new ObjectManager($this);
        $this->categoryManagement = $objectManager->getObject(
            CategoryManagement::class,
            [
                'sharedCatalogInvalidation' => $this->sharedCatalogInvalidation,
                'catalogPermissionManagement' => $this->catalogPermissionManagement,
                'sharedCatalogAssignment' => $this->sharedCatalogAssignment,
                'storeCategories' => $this->storeCategories,
            ]
        );
    }

    /**
     * Test for getCategories method.
     *
     * @return void
     */
    public function testGetCategories()
    {
        $storeId = 1;
        $sharedCatalogId = 2;
        $customerGroupId = 5;

        $sharedCatalog = $this->createMock(SharedCatalogInterface::class);
        $sharedCatalog->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $sharedCatalog->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);

        $this->sharedCatalogInvalidation->expects($this->once())
            ->method('checkSharedCatalogExist')
            ->with($sharedCatalogId)
            ->willReturn($sharedCatalog);
        $this->storeCategories->expects($this->once())
            ->method('getCategoryIds')
            ->with($storeId)
            ->willReturn([4, 5, 6]);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('getAllowedCategoriesIds')
            ->with($customerGroupId)
            ->willReturn([6]);

        $categories = $this->categoryManagement->getCategories($sharedCatalogId);
        $this->assertEquals([6], $categories);
    }

    /**
     * Test for assignCategories method.
     *
     * @return void
     */
    public function testAssignCategories()
    {
        $sharedCatalogId = 1;
        $categoryId = 2;
        $customerGroupId = 5;

        $sharedCatalog = $this->createMock(SharedCatalogInterface::class);
        $this->sharedCatalogInvalidation->expects($this->once())
            ->method('checkSharedCatalogExist')
            ->with($sharedCatalogId)
            ->willReturn($sharedCatalog);

        $category = $this->createMock(CategoryInterface::class);
        $category->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);
        $this->storeCategories->expects($this->once())
            ->method('getCategoryIds')
            ->with()
            ->willReturn([2, 3, 4]);

        $sharedCatalog->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('setAllowPermissions')
            ->with([$categoryId], [$customerGroupId]);

        $assignResult = $this->categoryManagement->assignCategories($sharedCatalogId, [$category]);
        $this->assertTrue($assignResult);
    }

    /**
     * Test for assignCategories method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested categories don't exist: 2
     */
    public function testAssignCategoriesWithException()
    {
        $sharedCatalogId = 1;
        $categoryId = 2;

        $sharedCatalog = $this->createMock(SharedCatalogInterface::class);
        $this->sharedCatalogInvalidation->expects($this->once())
            ->method('checkSharedCatalogExist')
            ->with($sharedCatalogId)
            ->willReturn($sharedCatalog);

        $category = $this->createMock(CategoryInterface::class);
        $category->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);
        $this->storeCategories->expects($this->once())
            ->method('getCategoryIds')
            ->with()
            ->willReturn([3, 4]);

        $sharedCatalog->expects($this->never())
            ->method('getCustomerGroupId');
        $this->catalogPermissionManagement->expects($this->never())
            ->method('setAllowPermissions');

        $assignResult = $this->categoryManagement->assignCategories($sharedCatalogId, [$category]);
        $this->assertTrue($assignResult);
    }

    /**
     * Test for unassignCategories method.
     *
     * @return void
     */
    public function testUnassignCategories()
    {
        $sharedCatalogId = 1;
        $categoryId = 2;
        $customerGroupId = 5;
        $storeId = 6;

        $sharedCatalog = $this->createMock(SharedCatalogInterface::class);
        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())
            ->method('checkSharedCatalogExist')
            ->with($sharedCatalogId)
            ->willReturn($sharedCatalog);

        $category = $this->createMock(CategoryInterface::class);
        $category->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);

        $this->storeCategories->expects($this->atLeastOnce())
            ->method('getCategoryIds')
            ->with()
            ->willReturn([2, 3, 4]);

        $sharedCatalog->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $sharedCatalog->expects($this->once())
            ->method('getType')
            ->willReturn(SharedCatalogInterface::TYPE_PUBLIC);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('setDenyPermissions')
            ->with([$categoryId], [$customerGroupId, \Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID]);
        $this->sharedCatalogAssignment->expects($this->once())
            ->method('unassignProductsForCategories')
            ->with($sharedCatalogId, [$categoryId]);
        $sharedCatalog->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->catalogPermissionManagement->expects($this->once())
            ->method('getAllowedCategoriesIds')
            ->with($customerGroupId)
            ->willReturn([3]);

        $unassignResult = $this->categoryManagement->unassignCategories($sharedCatalogId, [$category]);
        $this->assertTrue($unassignResult);
    }
}
