<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Unit\Model;

/**
 * Test for class SharedCatalogLocator.
 */
class SharedCatalogLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator
     */
    private $model;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogRepository = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\SharedCatalog\Model\SharedCatalogLocator::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
            ]
        );
    }

    /**
     * Test getSharedCatalogByCustomerGroup method.
     *
     * @return void
     */
    public function testGetSharedCatalogByCustomerGroup()
    {
        $customerGroupId = 1;
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResult = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::CUSTOMER_GROUP_ID, $customerGroupId)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn(1);
        $searchResult->expects($this->once())->method('getItems')->willReturn([$sharedCatalog]);

        $this->assertSame($sharedCatalog, $this->model->getSharedCatalogByCustomerGroup($customerGroupId));
    }

    /**
     * Test getSharedCatalogByCustomerGroup method throws exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such shared catalog entity
     */
    public function testGetSharedCatalogByCustomerGroupWithException()
    {
        $customerGroupId = 1;
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResult = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('addFilter')
            ->with(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::CUSTOMER_GROUP_ID, $customerGroupId)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn(0);

        $this->assertSame($sharedCatalog, $this->model->getSharedCatalogByCustomerGroup($customerGroupId));
    }
}
