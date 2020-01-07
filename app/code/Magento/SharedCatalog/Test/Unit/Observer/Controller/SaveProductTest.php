<?php
namespace Magento\SharedCatalog\Test\Unit\Observer\Controller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SharedCatalog\Api\CategoryManagementInterface;
use Magento\SharedCatalog\Api\Data\SearchResultsInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\ProductManagementInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\SharedCatalog\Model\ProductSharedCatalogsLoader;
use Magento\SharedCatalog\Observer\Controller\SaveProduct;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for Observer Controller\SaveProduct.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductManagementInterface|MockObject
     */
    private $productSharedCatalogManagement;

    /**
     * @var SaveProduct|MockObject
     */
    private $saveProductObserver;

    /**
     * @var ProductSharedCatalogsLoader|MockObject
     */
    private $productSharedCatalogsLoader;

    /**
     * @var SearchResultsInterface|MockObject
     */
    private $searchResults;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /**
     * @var CategoryManagementInterface|MockObject
     */
    private $categoryManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->method('addFilter')
            ->willReturnSelf();
        $searchCriteriaBuilder->method('create')
            ->willReturn($searchCriteria);
        $this->searchResults = $this->createMock(SearchResultsInterface::class);
        $sharedCatalogRepository = $this->createMock(SharedCatalogRepositoryInterface::class);
        $sharedCatalogRepository->method('getList')
            ->with($searchCriteria)
            ->willReturn($this->searchResults);

        $this->productSharedCatalogManagement = $this->createMock(ProductManagementInterface::class);
        $this->productSharedCatalogsLoader = $this->createMock(ProductSharedCatalogsLoader::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->categoryManagement = $this->createMock(CategoryManagementInterface::class);

        $this->saveProductObserver = (new ObjectManager($this))->getObject(
            SaveProduct::class,
            [
                'productSharedCatalogManagement' => $this->productSharedCatalogManagement,
                'sharedCatalogRepository' => $sharedCatalogRepository,
                'searchCriteriaBuilder' => $searchCriteriaBuilder,
                'productSharedCatalogsLoader' => $this->productSharedCatalogsLoader,
                'categoryRepository' => $this->categoryRepository,
                'categoryManagement' => $this->categoryManagement,
            ]
        );
    }

    /**
     * @dataProvider executeDataProvider
     * @param array $sharedCatalogIds
     * @param array $tierPrices
     * @param array $assignedSharedCatalogIds
     * @param array $forAssignIds
     * @param array $forUnassignIds
     * @return void
     */
    public function testExecute(
        array $sharedCatalogIds,
        array $tierPrices,
        array $assignedSharedCatalogIds,
        array $forAssignIds,
        array $forUnassignIds
    ) {
        $sku = 'sku1';
        $categoryId = 1;

        $product = $this->createMock(Product::class);
        $product->method('getSku')
            ->willReturn($sku);
        $product->method('getData')
            ->willReturnMap([
                ['shared_catalog', null, $sharedCatalogIds],
                ['tier_price', null, $tierPrices]
            ]);
        $product->method('getCategoryIds')
            ->willReturn([$categoryId]);
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $event->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);
        $observer = $this->createMock(Observer::class);
        $observer->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);

        $category = $this->createMock(CategoryInterface::class);
        $this->categoryRepository->method('get')
            ->with($categoryId)
            ->willReturn($category);
        $this->categoryManagement->expects($this->exactly(count($forAssignIds)))
            ->method('assignCategories')
            ->withConsecutive(...array_reduce($forAssignIds, function (array $carry, int $item) use ($category) {
                $carry[] = [$item, [$category]];
                return $carry;
            }, []))
            ->willReturn(true);

        $sharedCatalogs = [];
        foreach ($forAssignIds as $id) {
            $sharedCatalogs[$id] = $this->createMock(SharedCatalogInterface::class);
            $sharedCatalogs[$id]->method('getId')
                ->willReturn($id);
            $sharedCatalogs[$id]->method('getCustomerGroupId')
                ->willReturn($id);
        }
        $this->searchResults->method('getItems')
            ->willReturn($sharedCatalogs);

        $this->productSharedCatalogsLoader->expects($this->atLeastOnce())
            ->method('getAssignedSharedCatalogs')
            ->with($sku)
            ->willReturn(array_flip($assignedSharedCatalogIds));
        $this->productSharedCatalogManagement->expects($this->exactly(count($forAssignIds)))
            ->method('assignProducts')
            ->withConsecutive(...array_reduce($forAssignIds, function (array $carry, int $item) use ($product) {
                $carry[] = [$item, [$product]];
                return $carry;
            }, []))
            ->willReturn(true);
        $this->productSharedCatalogManagement->expects($this->exactly(count($forUnassignIds)))
            ->method('unassignProducts')
            ->withConsecutive(...array_reduce($forUnassignIds, function (array $carry, int $item) use ($product) {
                $carry[] = [$item, [$product]];
                return $carry;
            }, []))
            ->willReturn(true);

        $this->saveProductObserver->execute($observer);
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [
                [],
                [],
                [],
                [],
                [],
            ],
            [
                [1, 2],
                [],
                [],
                [1, 2],
                [],
            ],
            [
                [],
                [],
                [1, 2],
                [],
                [1, 2],
            ],
            [
                [1, 2],
                [],
                [2, 3],
                [1],
                [3],
            ],
            [
                [1, 2],
                [
                    ['cust_group' => 2],
                    ['cust_group' => 3],
                ],
                [],
                [1, 2, 3],
                [],
            ],
        ];
    }
}
