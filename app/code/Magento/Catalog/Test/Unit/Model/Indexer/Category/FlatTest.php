<?php
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category;

class FlatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\Action\FullFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\Action\RowsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowsMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Magento\Framework\Indexer\CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheContextMock;

    protected function setUp()
    {
        $this->fullMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Category\Flat\Action\FullFactory::class,
            ['create']
        );

        $this->rowsMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Category\Flat\Action\RowsFactory::class,
            ['create']
        );

        $this->indexerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'load', 'isInvalid', 'isWorking', '__wakeup']
        );

        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerRegistryMock
        );

        $this->cacheContextMock = $this->createMock(\Magento\Framework\Indexer\CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            \Magento\Catalog\Model\Indexer\Category\Flat::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    public function testExecuteWithIndexerInvalid()
    {
        $this->indexerMock->expects($this->once())->method('isInvalid')->will($this->returnValue(true));
        $this->prepareIndexer();

        $this->rowsMock->expects($this->never())->method('create');

        $this->model->execute([1, 2, 3]);
    }

    public function testExecuteWithIndexerWorking()
    {
        $ids = [1, 2, 3];

        $this->indexerMock->expects($this->once())->method('isInvalid')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('isWorking')->will($this->returnValue(true));
        $this->prepareIndexer();

        $rowMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Category\Flat\Action\Rows::class,
            ['reindex']
        );
        $rowMock->expects($this->at(0))->method('reindex')->with($ids, true)->will($this->returnSelf());
        $rowMock->expects($this->at(1))->method('reindex')->with($ids, false)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(\Magento\Catalog\Model\Category::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    public function testExecuteWithIndexerNotWorking()
    {
        $ids = [1, 2, 3];

        $this->indexerMock->expects($this->once())->method('isInvalid')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('isWorking')->will($this->returnValue(false));
        $this->prepareIndexer();

        $rowMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Category\Flat\Action\Rows::class,
            ['reindex']
        );
        $rowMock->expects($this->once())->method('reindex')->with($ids, false)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(\Magento\Catalog\Model\Category::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }

    public function testExecuteFull()
    {
        /** @var \Magento\Catalog\Model\Indexer\Category\Flat\Action\Full $categoryIndexerFlatFull */
        $categoryIndexerFlatFull = $this->createMock(\Magento\Catalog\Model\Indexer\Category\Flat\Action\Full::class);
        $this->fullMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryIndexerFlatFull);
        $categoryIndexerFlatFull->expects($this->once())
            ->method('reindexAll');
        $this->cacheContextMock->expects($this->once())
            ->method('registerTags')
            ->with([\Magento\Catalog\Model\Category::CACHE_TAG]);
        $this->model->executeFull();
    }
}
