<?php

namespace Magento\SharedCatalog\Test\Unit\Model\Configure;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for model Configure\Category.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productSharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\CatalogPermissionManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPermissionManagement;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\Configure\Category
     */
    private $category;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->productSharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\ProductManagementInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->catalogPermissionManagement = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\CatalogPermissionManagement::class
        )
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->category = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Configure\Category::class,
            [
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'productSharedCatalogManagement' => $this->productSharedCatalogManagement,
                'catalogPermissionManagement' => $this->catalogPermissionManagement,
            ]
        );
    }

    /**
     * Test for saveConfiguredCategories().
     *
     * @return void
     */
    public function testSaveConfiguredCategories()
    {
        $sharedCatalogId = 34;
        $storeId = 3;
        $customerGroupId = 5;
        $productSkus = ['sku_1', 'sku_2', 'sku_3'];
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $currentStorage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogRepository->expects($this->once())->method('get')->willReturn($sharedCatalog);
        $currentStorage->expects($this->once())->method('getAssignedProductSkus')->willReturn($productSkus);
        $currentStorage->expects($this->once())->method('getAssignedCategoriesIds')->willReturn([7, 9]);
        $currentStorage->expects($this->once())->method('getUnassignedCategoriesIds')->willReturn([12, 13]);
        $sharedCatalog->expects($this->once())->method('getStoreId')->willReturn(null);
        $sharedCatalog->expects($this->once())->method('setStoreId')->with($storeId)->willReturnSelf();
        $this->sharedCatalogRepository->expects($this->once())->method('save')->with($sharedCatalog)->willReturn(1);
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $sharedCatalog->expects($this->once())
            ->method('getType')
            ->willReturn(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('setDenyPermissions')
            ->with([12, 13], [1 => 0, 0 => 5]);
        $this->catalogPermissionManagement->expects($this->once())
            ->method('setAllowPermissions')
            ->with([7, 9], [1 => 0, 0 => 5]);
        $this->productSharedCatalogManagement->expects($this->once())
            ->method('reassignProducts')
            ->with($sharedCatalog, $productSkus)
            ->willReturnSelf();

        $this->assertEquals(
            $sharedCatalog,
            $this->category->saveConfiguredCategories($currentStorage, $sharedCatalogId, $storeId)
        );
    }
}
