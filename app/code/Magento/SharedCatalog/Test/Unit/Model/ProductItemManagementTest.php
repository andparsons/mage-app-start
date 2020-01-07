<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for ProductItemManagement model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductItemManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemFactory;

    /**
     * @var \Magento\SharedCatalog\Model\TierPriceManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogTierPriceManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogProductsLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductsLoader;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemResource;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemManagement
     */
    private $productItemManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogTierPriceManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\TierPriceManagement::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductsLoader = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogProductsLoader::class)
            ->disableOriginalConstructor()->getMock();
        $this->productItemResource = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\ProductItem::class)
            ->disableOriginalConstructor()->getMock();

        $this->productItemManagement = (new ObjectManager($this))->getObject(
            \Magento\SharedCatalog\Model\ProductItemManagement::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sharedCatalogProductItemRepository' => $this->sharedCatalogProductItemRepository,
                'sharedCatalogProductItemFactory' => $this->sharedCatalogProductItemFactory,
                'sharedCatalogTierPriceManagement' => $this->sharedCatalogTierPriceManagement,
                'sharedCatalogManagement' => $this->sharedCatalogManagement,
                'sharedCatalogProductsLoader' => $this->sharedCatalogProductsLoader,
                'productItemResource' => $this->productItemResource,
                'batchSize' => 2,
            ]
        );
    }

    /**
     * Test for deleteItems method.
     *
     * @return void
     */
    public function testDeleteItems()
    {
        $customerGroupId = 1;
        $productSkus = ['SKU1', 'SKU2'];
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $sharedCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $sharedCatalog->expects($this->atLeastOnce())
            ->method('getType')->willReturn(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC);
        $this->searchCriteriaBuilder->expects($this->exactly(4))->method('addFilter')
            ->withConsecutive(
                ['customer_group_id', $customerGroupId, 'eq'],
                ['sku', $productSkus, 'in'],
                ['customer_group_id', 0, 'eq'],
                ['sku', $productSkus, 'in']
            )->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $searchCriteria->expects($this->atLeastOnce())->method('setPageSize')->willReturnSelf();
        $searchCriteria->expects($this->atLeastOnce())->method('setCurrentPage')->with(1)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->exactly(2))->method('create')->willReturn($searchCriteria);
        $searchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemRepository->expects($this->exactly(2))
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults->expects($this->exactly(2))->method('getItems')->willReturn([$productItem, $productItem]);
        $searchResults->expects($this->any())->method('getTotalCount')->willReturn(2);
        $productItem->expects($this->exactly(2))->method('getSku')
            ->willReturnOnConsecutiveCalls($productSkus[0], $productSkus[1]);
        $this->sharedCatalogTierPriceManagement->expects($this->once())->method('deleteProductTierPrices')
            ->with($sharedCatalog, $productSkus, true);
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('deleteItems')->with([$productItem, $productItem]);

        $this->productItemManagement->deleteItems($sharedCatalog, $productSkus);
    }

    /**
     * Test for updateTierPrices method.
     *
     * @return void
     */
    public function testUpdateTierPrices()
    {
        $productSku = 'SKU1';
        $tierPricesData = ['tier_prices_data'];
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $product->expects($this->exactly(2))->method('getSku')->willReturn($productSku);
        $this->sharedCatalogTierPriceManagement->expects($this->once())
            ->method('deleteProductTierPrices')->with($sharedCatalog, [$productSku]);
        $this->sharedCatalogTierPriceManagement->expects($this->once())
            ->method('updateProductTierPrices')->with($sharedCatalog, $productSku, $tierPricesData);
        $this->productItemManagement->updateTierPrices($sharedCatalog, $product, $tierPricesData);
    }

    /**
     * Test for deleteTierPricesBySku method.
     *
     * @return void
     */
    public function testDeleteTierPricesBySku()
    {
        $productSkus = ['SKU1', 'SKU2'];
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogTierPriceManagement->expects($this->once())
            ->method('deleteProductTierPrices')->with($sharedCatalog, $productSkus);
        $this->productItemManagement->deleteTierPricesBySku($sharedCatalog, $productSkus);
    }

    /**
     * Test for addItems method.
     *
     * @return void
     */
    public function testAddItems()
    {
        $customerGroupId = 1;
        $productSkus = ['SKU1', 'SKU2'];
        $this->searchCriteriaBuilder->expects($this->exactly(2))->method('addFilter')
            ->withConsecutive(['customer_group_id', $customerGroupId, 'eq'], ['sku', $productSkus, 'in'])
            ->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $searchCriteria->expects($this->atLeastOnce())->method('setPageSize')->willReturnSelf();
        $searchCriteria->expects($this->atLeastOnce())->method('setCurrentPage')->with(1)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults->expects($this->once())->method('getItems')->willReturn([$productItem]);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(1);
        $productItem->expects($this->once())->method('getSku')->willReturn($productSkus[1]);
        $this->productItemResource->expects($this->once())
            ->method('createItems')->with([$productSkus[0]], $customerGroupId);
        $this->productItemManagement->addItems($customerGroupId, $productSkus);
    }

    /**
     * Test for addItems method with LocalizedException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot load product items for shared catalog
     */
    public function testAddItemsWithLocalizedException()
    {
        $customerGroupId = 1;
        $productSkus = ['SKU1', 'SKU2'];
        $this->searchCriteriaBuilder->expects($this->exactly(2))->method('addFilter')
            ->withConsecutive(['customer_group_id', $customerGroupId, 'eq'], ['sku', $productSkus, 'in'])
            ->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $exception = new \InvalidArgumentException(__('test'));
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willThrowException($exception);

        $this->productItemManagement->addItems($customerGroupId, $productSkus);
    }

    /**
     * Test for saveItem method.
     *
     * @return void
     */
    public function testSaveItem()
    {
        $customerGroupId = 1;
        $productSku = 'SKU1';
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemFactory->expects($this->once())->method('create')->willReturn($productItem);
        $productItem->expects($this->once())->method('setSku')->with($productSku)->willReturnSelf();
        $productItem->expects($this->once())->method('setCustomerGroupId')->with($customerGroupId)->willReturnSelf();
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('save')->with($productItem)->willReturn(2);
        $this->productItemManagement->saveItem($productSku, $customerGroupId);
    }

    /**
     * Test for deletePricesForPublicCatalog method.
     *
     * @return void
     */
    public function testDeletePricesForPublicCatalog()
    {
        $productSkus = ['SKU1', 'SKU2'];
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')->with('customer_group_id', 0, 'eq')->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $searchCriteria->expects($this->atLeastOnce())->method('setPageSize')->willReturnSelf();
        $searchCriteria->expects($this->atLeastOnce())->method('setCurrentPage')->with(1)->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults->expects($this->once())->method('getItems')->willReturn([$productItem, $productItem]);
        $searchResults->expects($this->any())->method('getTotalCount')->willReturn(2);
        $productItem->expects($this->exactly(2))->method('getSku')
            ->willReturnOnConsecutiveCalls($productSkus[0], $productSkus[1]);
        $this->sharedCatalogTierPriceManagement->expects($this->once())
            ->method('deletePublicTierPrices')->with($productSkus);
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('deleteItems')->with([$productItem, $productItem]);
        $this->productItemManagement->deletePricesForPublicCatalog();
    }

    /**
     * Test for addPricesForPublicCatalog method.
     *
     * @return void
     */
    public function testAddPricesForPublicCatalog()
    {
        $customerGroupId = 1;
        $productSkus = ['SKU1', 'SKU2'];
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($sharedCatalog);
        $sharedCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->sharedCatalogProductsLoader->expects($this->once())
            ->method('getAssignedProductsSkus')->with($customerGroupId)->willReturn($productSkus);
        $this->sharedCatalogTierPriceManagement->expects($this->once())
            ->method('addPricesForPublicCatalog')->with($customerGroupId, $productSkus);
        $this->searchCriteriaBuilder->expects($this->exactly(2))->method('addFilter')
            ->withConsecutive(['customer_group_id', 0, 'eq'], ['sku', $productSkus, 'in'])
            ->willReturnSelf();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults->expects($this->once())->method('getItems')->willReturn([$productItem]);
        $productItem->expects($this->once())->method('getSku')->willReturn($productSkus[1]);
        $this->productItemResource->expects($this->once())
            ->method('createItems')->with([$productSkus[0]], 0);
        $this->productItemManagement->addPricesForPublicCatalog();
    }
}
