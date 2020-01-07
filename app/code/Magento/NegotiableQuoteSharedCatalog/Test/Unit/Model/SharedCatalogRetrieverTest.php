<?php

namespace Magento\NegotiableQuoteSharedCatalog\Test\Unit\Model;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Unit test for SharedCatalogRetrieverTest model.
 */
class SharedCatalogRetrieverTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalogRetriever
     */
    private $model;

    /**
     * @inheritdoc
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
            \Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalogRetriever::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
            ]
        );
    }

    /**
     * Test sharedCatalogExists method.
     *
     * @param int $totalCount
     * @param bool $expectedResult
     * @return void
     * @dataProvider sharedCatalogExistsDataProvider
     */
    public function testSharedCatalogExists($totalCount, $expectedResult)
    {
        $customerGroupId = 6;
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(SharedCatalogInterface::CUSTOMER_GROUP_ID, $customerGroupId)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn($totalCount);

        $this->assertEquals($expectedResult, $this->model->isSharedCatalogPresent($customerGroupId));
    }

    /**
     * Data provider fot sharedCatalogExists method.
     *
     * @return array
     */
    public function sharedCatalogExistsDataProvider()
    {
        return [
            [1, true],
            [0, false]
        ];
    }

    /**
     * Test getPublicCatalog method.
     *
     * @return void
     */
    public function testGetPublicCatalog()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(SharedCatalogInterface::TYPE, SharedCatalogInterface::TYPE_PUBLIC)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(1);
        $searchResults->expects($this->once())->method('getItems')->willReturn([$sharedCatalog]);

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            $this->model->getPublicCatalog()
        );
    }

    /**
     * Test getPublicCatalog method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such public catalog entity
     */
    public function testGetPublicCatalogWithException()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(SharedCatalogInterface::TYPE, SharedCatalogInterface::TYPE_PUBLIC)
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(0);

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            $this->model->getPublicCatalog()
        );
    }
}
