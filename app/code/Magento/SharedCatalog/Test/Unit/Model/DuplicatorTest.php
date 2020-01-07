<?php
namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk;

/**
 * Unit test for DuplicateHandler.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DuplicatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryManagement;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productManagement;

    /**
     * @var \Magento\SharedCatalog\Model\CatalogPermissionManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPermissionManagement;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var ScheduleBulk|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleBulk;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextInterface;

    /**
     * @var \Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceLoader;

    /**
     * @var \Magento\SharedCatalog\Model\Duplicator
     */
    private $duplicateManager;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogDuplicationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogDuplication;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->categoryManagement = $this->getMockBuilder(\Magento\SharedCatalog\Api\CategoryManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productManagement = $this->getMockBuilder(\Magento\SharedCatalog\Api\ProductManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->catalogPermissionManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\CatalogPermissionManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scheduleBulk = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContextInterface = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPriceLoader = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogDuplication = $this
            ->getMockForAbstractClass(\Magento\SharedCatalog\Api\SharedCatalogDuplicationInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->duplicateManager = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Duplicator::class,
            [
                'categoryManagement' => $this->categoryManagement,
                'productManagement' => $this->productManagement,
                'catalogPermissionManagement' => $this->catalogPermissionManagement,
                'productRepository' => $this->productRepository,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'scheduleBulk' => $this->scheduleBulk,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'userContextInterface' => $this->userContextInterface,
                'tierPriceLoader' => $this->tierPriceLoader,
                'sharedCatalogDuplication' => $this->sharedCatalogDuplication
            ]
        );
    }

    /**
     * Unit test for execute().
     *
     * @return void
     */
    public function testExecute()
    {
        $idOriginal = 1;
        $idDuplicated = 2;
        $oldStoreId = 3;
        $categoryIds = [4];
        $oldCatalogCustomerGroupId = 5;
        $newCatalogCustomerGroupId = 6;
        $productSkus = ['sku'];
        $oldCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $oldCatalog->expects($this->atLeastOnce())->method('getStoreId')->willReturn($oldStoreId);
        $oldCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')
            ->willReturn($oldCatalogCustomerGroupId);
        $newCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $newCatalog->expects($this->atLeastOnce())->method('setStoreId')->with($oldStoreId)->willReturnSelf();
        $newCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')
            ->willReturn($newCatalogCustomerGroupId);
        $this->sharedCatalogRepository->expects($this->atLeastOnce())->method('get')
            ->willReturnOnConsecutiveCalls($oldCatalog, $newCatalog);
        $this->sharedCatalogRepository->expects($this->atLeastOnce())->method('save')->with($newCatalog);
        $this->categoryManagement->expects($this->atLeastOnce())->method('getCategories')->with($idOriginal)
            ->willReturn($categoryIds);
        $this->catalogPermissionManagement->expects($this->atLeastOnce())->method('setAllowPermissions')
            ->with($categoryIds, [$newCatalogCustomerGroupId]);
        $this->productManagement->expects($this->atLeastOnce())->method('getProducts')->with($idOriginal)
            ->willReturn($productSkus);
        $tierPrice = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tierPriceLoader->expects($this->atLeastOnce())->method('load')->willReturn([$tierPrice]);
        $this->sharedCatalogDuplication->expects($this->atLeastOnce())->method('assignProductsToDuplicate')
            ->with($idDuplicated, $productSkus)->willReturnSelf();
        $this->userContextInterface->expects($this->atLeastOnce())->method('getUserId')->willReturn(1);
        $this->scheduleBulk->expects($this->atLeastOnce())->method('execute');

        $this->duplicateManager->duplicateCatalog($idOriginal, $idDuplicated);
    }
}
