<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

/**
 * Management unit test.
 */
class ManagementTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\SharedCatalog\Model\SharedCatalogFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Management
     */
    private $management;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->sharedCatalogRepository =
            $this->createMock(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class);
        $this->sharedCatalogFactory = $this->createMock(\Magento\SharedCatalog\Model\SharedCatalogFactory::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->management = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Management::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'sharedCatalogFactory' => $this->sharedCatalogFactory
            ]
        );
    }

    /**
     * Test getPublicCatalog.
     *
     * @return void
     */
    public function testGetPublicCatalog()
    {
        $this->prepareMocksGetPublicCatalog();

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            $this->management->getPublicCatalog()
        );
    }

    /**
     * Test getPublicCatalog with NoSuchEntityException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetPublicCatalogWithNoSuchEntityException()
    {
        $this->prepareMocksGetPublicCatalogWithNoSuchEntityException();

        $this->management->getPublicCatalog();
    }

    /**
     * Test isPublicCatalogExists.
     *
     * @return void
     */
    public function testIsPublicCatalogExist()
    {
        $this->prepareMocksGetPublicCatalog();

        $this->assertTrue($this->management->isPublicCatalogExist());
    }

    /**
     * Test isPublicCatalogExists with NoSuchEntityException.
     *
     * @return void
     */
    public function testIsPublicCatalogExistWithNoSuchEntityException()
    {
        $this->prepareMocksGetPublicCatalogWithNoSuchEntityException();

        $this->assertNotTrue($this->management->isPublicCatalogExist());
    }

    /**
     * Prepare mocks getPublicCatalog.
     *
     * @return void
     */
    private function prepareMocksGetPublicCatalog()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->willReturnSelf();
        $sharedCatalog = $this->createMock(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class);
        $searchResults = $this->createMock(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(2);
        $searchResults->expects($this->once())->method('getItems')->willReturn([$sharedCatalog, $sharedCatalog]);
        $this->sharedCatalogRepository->expects($this->once())->method('getList')->with($searchCriteria)
            ->willReturn($searchResults);
    }

    /**
     * Prepare mocks getPublicCatalog with NoSuchEntityException.
     *
     * @return void
     */
    private function prepareMocksGetPublicCatalogWithNoSuchEntityException()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')->willReturnSelf();
        $searchResults = $this->createMock(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(0);
        $this->sharedCatalogRepository->expects($this->once())->method('getList')->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults->expects($this->never())->method('getItems');
    }
}
