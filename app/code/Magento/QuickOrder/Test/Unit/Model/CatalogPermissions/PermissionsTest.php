<?php
namespace Magento\QuickOrder\Test\Unit\Model\CatalogPermissions;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogPermissions\Model\Permission;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Unit tests for Permissions model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PermissionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\QuickOrder\Model\CatalogPermissions\Permissions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissions;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\CatalogPermissions\Model\Permission\Index|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionIndexMock;

    /**
     * @var \Magento\CatalogPermissions\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPermissionsDataMock;

    /**
     * @var \Magento\CatalogPermissions\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionConfigMock;

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
        $this->userContextMock = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->permissionIndexMock = $this->getMockBuilder(\Magento\CatalogPermissions\Model\Permission\Index::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogPermissionsDataMock = $this->getMockBuilder(\Magento\CatalogPermissions\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->permissionConfigMock = $this->getMockBuilder(\Magento\CatalogPermissions\App\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->permissions = $this->objectManagerHelper->getObject(
            \Magento\QuickOrder\Model\CatalogPermissions\Permissions::class,
            [
                'userContext' => $this->userContextMock,
                'customerRepository' => $this->customerRepositoryMock,
                'permissionIndex' => $this->permissionIndexMock,
                'catalogPermissionsData' => $this->catalogPermissionsDataMock,
                'permissionConfig' => $this->permissionConfigMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Add customer mock.
     *
     * @return void
     */
    private function addCustomerMock()
    {
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getGroupId']
            )
            ->getMockForAbstractClass();
        $customerMock->expects($this->once())->method('getGroupId')->willReturn(1);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customerMock);
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn(1);
    }

    /**
     * Test for isProductPermissionsValid method when CatalogPermissions is disabled.
     *
     * @return void
     */
    public function testIsProductPermissionsValidDisabled()
    {
        $this->permissionConfigMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertEquals(true, $this->permissions->isProductPermissionsValid($productMock));
    }

    /**
     * Test for isProductPermissionsValid method when product do not assigned to categories.
     *
     * @return void
     */
    public function testIsProductPermissionsValidNoCategories()
    {
        $this->permissionConfigMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $productMock->expects($this->once())->method('getData')->with('category_ids')->willReturn(null);

        $this->assertEquals(true, $this->permissions->isProductPermissionsValid($productMock));
    }

    /**
     * Test for isProductPermissionsValid method.
     *
     * @param bool $result
     * @param bool $isAllowedCategoryView
     * @param array $permissionsData
     * @return void
     * @dataProvider isProductPermissionsValidDataProvider
     */
    public function testIsProductPermissionsValid($result, $isAllowedCategoryView, $permissionsData)
    {
        $this->addCustomerMock();
        $this->permissionConfigMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $productMock->expects($this->once())->method('getData')->with('category_ids')->willReturn([1, 2]);
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'fetchAll',
                    'select',
                    'from',
                    'where'
                ]
            )
            ->getMockForAbstractClass();
        $permissionResourceMock = $this->getMockBuilder(
            \Magento\CatalogPermissions\Model\ResourceModel\Permission\Index::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTable'])
            ->getMock();
        $permissionResourceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->permissionIndexMock->expects($this->exactly(2))->method('getResource')
            ->willReturn($permissionResourceMock);
        $permissionResourceMock->expects($this->once())->method('getTable')->willReturn('dummy_table');
        $connectionMock->expects($this->any())->method('select')->willReturnSelf();
        $connectionMock->expects($this->any())->method('from')->willReturnSelf();
        $connectionMock->expects($this->any())->method('where')->willReturnSelf();
        $connectionMock->expects($this->once())->method('fetchAll')->willReturn($permissionsData);
        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())->method('getWebsite')->willReturn($websiteMock);
        $websiteMock->expects($this->once())->method('getId')->willReturn(1);
        $this->catalogPermissionsDataMock->expects($this->any())->method('isAllowedCategoryView')
            ->willReturn($isAllowedCategoryView);

        $this->assertEquals($result, $this->permissions->isProductPermissionsValid($productMock));
    }

    /**
     * Data Provider for testIsProductPermissionsValid.
     *
     * @return array
     */
    public function isProductPermissionsValidDataProvider()
    {
        return [
            [
                true,
                true,
                [
                    ['grant_catalog_category_view' => Permission::PERMISSION_ALLOW]
                ]
            ],
            [
                true,
                true,
                []
            ],
            [
                false,
                true,
                [
                    ['grant_catalog_category_view' => Permission::PERMISSION_DENY]
                ]
            ]
        ];
    }

    /**
     * Test for applyPermissionsToProductCollection() method.
     *
     * @return void
     */
    public function testApplyPermissionsToProductCollection()
    {
        $websiteId = 1;
        $customerGroupId = 1;
        $catalogCategoryProductTableName = 'catalog_category_product';
        $restrictedCategoryIds = [1, 2];

        $this->addCustomerMock();
        $this->permissionConfigMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $productCollectionMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMock();
        $selectMock->method('getConnection')->willReturn($connectionMock);
        $productCollectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->permissionIndexMock->expects($this->once())->method('getRestrictedCategoryIds')
            ->with($customerGroupId, $websiteId)
            ->willReturn($restrictedCategoryIds);
        $productCollectionMock->expects($this->once())->method('getTable')->with($catalogCategoryProductTableName)
            ->willReturn($catalogCategoryProductTableName);
        $selectMock->expects($this->once())->method('joinLeft')->with(
            ['category_product' => $catalogCategoryProductTableName],
            'category_product.product_id = ' . ProductCollection::MAIN_TABLE_ALIAS
            . '.entity_id',
            []
        )->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->willReturnSelf();
        $selectMock->expects($this->once())->method('distinct');

        $this->permissions->applyPermissionsToProductCollection($productCollectionMock);
    }
}
