<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Unit\Model;

/**
 * Test for ProductItemRepository model.
 */
class ProductItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\ProductItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemFactory;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemResource;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\CollectionFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemCollectionFactory;

    /**
     * @var \Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterfaceFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemRepository
     */
    private $productItemRepository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogProductItemFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ProductItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemResource = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemCollectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\ProductItem\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->searchResultsFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->collectionProcessor = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productItemRepository = $objectManager->getObject(
            \Magento\SharedCatalog\Model\ProductItemRepository::class,
            [
                'sharedCatalogProductItemFactory' => $this->sharedCatalogProductItemFactory,
                'sharedCatalogProductItemResource' => $this->sharedCatalogProductItemResource,
                'sharedCatalogProductItemCollectionFactory' => $this->sharedCatalogProductItemCollectionFactory,
                'searchResultsFactory' => $this->searchResultsFactory,
                'collectionProcessor' => $this->collectionProcessor,
            ]
        );
    }

    /**
     * Test for save method.
     *
     * @return void
     */
    public function testSave()
    {
        $productItemId = 1;
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $productItem->expects($this->once())->method('getSku')->willReturn('SKU1');
        $productItem->expects($this->once())->method('getCustomerGroupId')->willReturn(2);
        $productItem->expects($this->atLeastOnce())->method('getId')->willReturn($productItemId);
        $this->sharedCatalogProductItemResource->expects($this->once())
            ->method('save')->with($productItem)->willReturn($productItem);
        $this->assertEquals($productItemId, $this->productItemRepository->save($productItem));
    }

    /**
     * Test for save method with InputException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage One or more input exceptions have occurred
     */
    public function testSaveWithInputException()
    {
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $productItem->expects($this->atLeastOnce())->method('getSku')->willReturn(null);
        $productItem->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn(null);
        $this->sharedCatalogProductItemResource->expects($this->never())->method('save');
        $this->productItemRepository->save($productItem);
    }

    /**
     * Test for save method with CouldNotSaveException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save ProductItem
     */
    public function testSaveWithCouldNotSaveException()
    {
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $productItem->expects($this->atLeastOnce())->method('getSku')->willReturn('SKU1');
        $productItem->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn(1);
        $this->sharedCatalogProductItemResource->expects($this->once())
            ->method('save')->with($productItem)->willThrowException(new \Exception('Exception message'));
        $this->productItemRepository->save($productItem);
    }

    /**
     * Test for get method.
     *
     * @return void
     */
    public function testGet()
    {
        $productItemId = 1;
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemFactory->expects($this->once())->method('create')->willReturn($productItem);
        $productItem->expects($this->once())->method('load')->with($productItemId)->willReturnSelf();
        $productItem->expects($this->once())->method('getId')->willReturn($productItemId);
        $this->assertEquals($productItem, $this->productItemRepository->get($productItemId));
    }

    /**
     * Test for get method with NoSuchEntityException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetWithNoSuchEntityException()
    {
        $productItemId = 1;
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemFactory->expects($this->once())->method('create')->willReturn($productItem);
        $productItem->expects($this->once())->method('load')->with($productItemId)->willReturnSelf();
        $productItem->expects($this->once())->method('getId')->willReturn(null);
        $this->productItemRepository->get($productItemId);
    }

    /**
     * Test for deleteById method.
     *
     * @return void
     */
    public function testDeleteById()
    {
        $productItemId = 1;
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemFactory->expects($this->once())->method('create')->willReturn($productItem);
        $productItem->expects($this->once())->method('load')->with($productItemId)->willReturnSelf();
        $productItem->expects($this->atLeastOnce())->method('getId')->willReturn($productItemId);
        $this->sharedCatalogProductItemResource->expects($this->once())
            ->method('delete')->with($productItem)->willReturnSelf();
        $this->assertTrue($this->productItemRepository->deleteById($productItemId));
    }

    /**
     * Test for delete method with StateException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete product with id 1
     */
    public function testDeleteWithStateException()
    {
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $productItem->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->sharedCatalogProductItemResource->expects($this->once())->method('delete')
            ->willThrowException(new \Exception('Exception message'));
        $this->productItemRepository->delete($productItem);
    }

    /**
     * Test for deleteItems method.
     *
     * @return void
     */
    public function testDeleteItems()
    {
        $productSkus = ['SKU1', 'SKU2'];
        $customerGroupIds = [1, 2];
        $productItemIds = [3, 4];
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $productItem->expects($this->exactly(2))->method('getSku')
            ->willReturnOnConsecutiveCalls($productSkus[0], $productSkus[1]);
        $productItem->expects($this->exactly(2))->method('getCustomerGroupId')
            ->willReturnOnConsecutiveCalls($customerGroupIds[0], $customerGroupIds[1]);
        $productItem->expects($this->exactly(2))->method('getId')
            ->willReturnOnConsecutiveCalls($productItemIds[0], $productItemIds[1]);
        $this->sharedCatalogProductItemResource->expects($this->exactly(2))->method('deleteItems')
            ->withConsecutive(
                [[$productSkus[0]], $customerGroupIds[0]],
                [[$productSkus[1]], $customerGroupIds[1]]
            );
        $this->assertTrue($this->productItemRepository->deleteItems([$productItem, $productItem]));
    }

    /**
     * Test for getList method.
     *
     * @return void
     */
    public function testGetList()
    {
        $collectionSize = 1;
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $searchResults->expects($this->once())->method('setSearchCriteria')->with($searchCriteria)->willReturnSelf();
        $collection = $this->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\ProductItem\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogProductItemCollectionFactory
            ->expects($this->once())->method('create')->willReturn($collection);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);
        $collection->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $productItem = $this->getMockBuilder(\Magento\SharedCatalog\Model\ProductItem::class)
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('getItems')->willReturn([$productItem]);
        $searchResults->expects($this->once())->method('setTotalCount')->with($collectionSize)->willReturnSelf();
        $searchResults->expects($this->once())->method('setItems')->with([$productItem])->willReturnSelf();
        $this->assertEquals($searchResults, $this->productItemRepository->getList($searchCriteria));
    }
}
