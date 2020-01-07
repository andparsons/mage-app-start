<?php
namespace Magento\Solr\Test\Unit\Model\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Solr\Model\Adminhtml\Source\IndexationMode;
use Magento\Solr\Model\Indexer\IndexerHandler;
use Magento\Store\Model\ScopeInterface;

class IndexerHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IndexerHandler
     */
    private $model;

    /**
     * @var \Magento\Solr\Model\Adapter\Solarium|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $batch;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolver;

    /**
     * @var \Magento\Framework\App\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeInterface;

    protected function setUp()
    {
        $this->adapter = $this->getMockBuilder(\Magento\Solr\Model\Adapter\Solarium::class)
            ->disableOriginalConstructor()
            ->getMock();

        $adapterFactory = $this->getMockBuilder(\Magento\Solr\Model\AdapterFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $adapterFactory->expects($this->any())
            ->method('createAdapter')
            ->willReturn($this->adapter);

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->batch = $this->getMockBuilder(\Magento\Framework\Indexer\SaveHandler\Batch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeResolver = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeResolverInterface::class,
            [],
            '',
            false
        );

        $this->scopeInterface = $this->getMockForAbstractClass(
            \Magento\Framework\App\ScopeInterface::class,
            [],
            '',
            false
        );

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Solr\Model\Indexer\IndexerHandler::class,
            [
                'adapterFactory' => $adapterFactory,
                'scopeConfig' => $this->scopeConfig,
                'batch' => $this->batch,
                'scopeResolver' => $this->scopeResolver
            ]
        );
    }

    public function testCleanIndex()
    {
        $dimensionValue = 'SomeDimension';

        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn($dimensionValue);

        $this->adapter->expects($this->once())
            ->method('deleteDocs')
            ->with(["store_id:{$dimensionValue}"]);

        $result = $this->model->cleanIndex([$dimension]);

        $this->assertEquals($this->model, $result);
    }

    public function testIsAvailable()
    {
        $this->adapter->expects($this->once())
            ->method('ping')
            ->willReturn(true);

        $result = $this->model->isAvailable();

        $this->assertTrue($result);
    }

    public function testDeleteIndex()
    {
        $dimensionName = IndexerHandler::SCOPE_FIELD_NAME;
        $dimensionValue = 3;
        $uniqueKey = 'someUniqueKey';
        $documentId = 123;
        $query = $uniqueKey . ':' . $documentId . '|' . $dimensionValue;

        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getName')
            ->willReturn($dimensionName);
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);
        $this->scopeResolver->expects($this->atLeastOnce())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($dimensionValue);

        $this->adapter->expects($this->once())
            ->method('getUniqueKey')
            ->willReturn($uniqueKey);
        $this->adapter->expects($this->once())
            ->method('deleteDocs')
            ->with([$query]);

        $this->scopeConfig->expects($this->any())->method('getValue')
            ->with($this->anything(), ScopeInterface::SCOPE_STORE, $dimensionValue)
            ->willReturn(IndexationMode::MODE_PARTIAL);
        $result = $this->model->deleteIndex([$dimension], new \ArrayIterator([$documentId]));

        $this->assertEquals($this->model, $result);
    }

    public function testSaveIndex()
    {
        $dimensionName = IndexerHandler::SCOPE_FIELD_NAME;
        $dimensionValue = 3;
        $documentId = 123;
        $documents = new \ArrayIterator([$documentId]);

        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->once())
            ->method('getName')
            ->willReturn($dimensionName);
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($dimensionValue);
        $this->scopeResolver->expects($this->atLeastOnce())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($dimensionValue);

        $this->batch->expects($this->once())
            ->method('getItems')
            ->with($documents, 500)
            ->willReturn([[]]);

        $this->adapter->expects($this->once())
            ->method('prepareDocsPerStore')
            ->with([], $dimensionValue)
            ->willReturn([$documentId]);
        $this->adapter->expects($this->once())
            ->method('addDocs')
            ->with([$documentId]);
        $this->adapter->expects($this->once())
            ->method('holdCommit');

        $result = $this->model->saveIndex([$dimension], $documents);

        $this->assertEquals($this->model, $result);
    }
}
