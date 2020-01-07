<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory;

/**
 * Repository unit test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogResource;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogCollectionFactory;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemManagement;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveHandler;

    /**
     * @var \Magento\SharedCatalog\Model\Repository
     */
    private $repository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogResource =
            $this->createMock(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog::class);
        $this->sharedCatalogCollectionFactory = $this->createPartialMock(
            \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory::class,
            ['create']
        );
        $this->searchResultsFactory = $this->createPartialMock(
            \Magento\SharedCatalog\Api\Data\SearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->sharedCatalogProductItemManagement =
            $this->createMock(\Magento\SharedCatalog\Api\ProductItemManagementInterface::class);
        $this->collectionProcessor =
            $this->createMock(\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class);
        $this->validator = $this->createMock(\Magento\SharedCatalog\Model\SharedCatalogValidator::class);
        $this->saveHandler = $this->createMock(\Magento\SharedCatalog\Model\SaveHandler::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->repository = $objectManager->getObject(
            \Magento\SharedCatalog\Model\Repository::class,
            [
                'sharedCatalogResource' => $this->sharedCatalogResource,
                'sharedCatalogCollectionFactory' => $this->sharedCatalogCollectionFactory,
                'searchResultsFactory' => $this->searchResultsFactory,
                'sharedCatalogProductItemManagement' => $this->sharedCatalogProductItemManagement,
                'collectionProcessor' => $this->collectionProcessor,
                'validator' => $this->validator,
                'saveHandler' => $this->saveHandler
            ]
        );
    }

    /**
     * Test save.
     *
     * @return void
     */
    public function testSave()
    {
        $id = 1;
        $sharedCatalog = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getData', 'setData', 'getId']
        );
        $sharedCatalog->expects($this->atLeastOnce())->method('getId')->willReturn($id);
        $sharedCatalog->expects($this->atLeastOnce())->method('getData')->willReturn([]);
        $sharedCatalog->expects($this->atLeastOnce())->method('setData')->willReturnSelf();
        $this->prepareMocksGet($sharedCatalog);
        $this->saveHandler->expects($this->once())->method('execute')->with($sharedCatalog)->willReturn($sharedCatalog);

        $this->assertEquals($id, $this->repository->save($sharedCatalog));
    }

    /**
     * Test get.
     *
     * @return void
     */
    public function testGet()
    {
        $sharedCatalog = $this->createMock(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class);
        $this->prepareMocksGet($sharedCatalog);

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            $this->repository->get(1)
        );
    }

    /**
     * Test delete.
     *
     * @return void
     */
    public function testDelete()
    {
        $sharedCatalog = $this->createMock(\Magento\SharedCatalog\Model\SharedCatalog::class);
        $this->prepareMocksDelete($sharedCatalog);

        $this->assertTrue($this->repository->delete($sharedCatalog));
    }

    /**
     * Test deleteById.
     *
     * @return void
     */
    public function testDeleteById()
    {
        $sharedCatalog = $this->createMock(\Magento\SharedCatalog\Model\SharedCatalog::class);
        $this->prepareMocksGet($sharedCatalog);
        $this->prepareMocksDelete($sharedCatalog);

        $this->assertTrue($this->repository->deleteById(1));
    }

    /**
     * Test testGetList.
     *
     * @return void
     */
    public function testGetList()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchResults = $this->createMock(\Magento\SharedCatalog\Api\Data\SearchResultsInterface::class);
        $searchResults->expects($this->once())->method('setSearchCriteria')->with($searchCriteria)->willReturnSelf();
        $searchResults->expects($this->once())->method('setTotalCount')->willReturnSelf();
        $searchResults->expects($this->once())->method('setItems')->willReturnSelf();
        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $sharedCatalogCollection =
            $this->createMock(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection::class);
        $sharedCatalogCollection->expects($this->once())->method('getSize')->willReturn(1);
        $sharedCatalogCollection->expects($this->once())->method('getItems')->willReturn([]);
        $this->sharedCatalogCollectionFactory->expects($this->once())->method('create')
            ->willReturn($sharedCatalogCollection);
        $this->collectionProcessor->expects($this->once())->method('process');

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SearchResultsInterface::class,
            $this->repository->getList($searchCriteria)
        );
    }

    /**
     * Prepare mocks get.
     *
     * @param SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject $sharedCatalog
     * @return void
     */
    private function prepareMocksGet($sharedCatalog)
    {
        $sharedCatalogCollection =
            $this->createMock(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection::class);
        $sharedCatalogCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $sharedCatalogCollection->expects($this->once())->method('getFirstItem')->willReturn($sharedCatalog);
        $this->sharedCatalogCollectionFactory->expects($this->once())->method('create')
            ->willReturn($sharedCatalogCollection);
    }

    /**
     * Prepare mocks delete.
     *
     * @param SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject $sharedCatalog
     * @return void
     */
    private function prepareMocksDelete($sharedCatalog)
    {
        $sharedCatalog->expects($this->once())->method('getId')->willReturn(1);
        $this->validator->expects($this->once())->method('isSharedCatalogPublic')->with($sharedCatalog)
            ->willReturn(true);
        $this->sharedCatalogProductItemManagement->expects($this->once())->method('deleteItems')->with($sharedCatalog);
        $this->sharedCatalogResource->expects($this->once())->method('delete');
    }
}
