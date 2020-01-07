<?php
namespace Magento\Solr\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Solr\Model\Adapter\Solarium;
use Magento\Solr\Model\Adminhtml\Source\IndexationMode;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class IndexerHandler implements IndexerInterface
{
    /**
     * Config key for indexation mode configuration
     */
    const COMMIT_MODE_XML_PATH = 'catalog/search/engine_commit_mode';

    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * @var Solarium
     */
    private $adapter;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    private $batch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @param \Magento\Solr\Model\AdapterFactoryInterface $adapterFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Indexer\SaveHandler\Batch $batch
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        \Magento\Solr\Model\AdapterFactoryInterface $adapterFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Indexer\SaveHandler\Batch $batch,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        array $data = [],
        $batchSize = 500
    ) {
        $this->adapter = $adapterFactory->createAdapter();
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
        $this->batch = $batch;
        $this->batchSize = $batchSize;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Get commit mode
     * @param Dimension $dimension
     * @return string
     */
    private function getCommitMode(Dimension $dimension)
    {
        return $this->scopeConfig->getValue(
            self::COMMIT_MODE_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $this->scopeResolver->getScope($dimension->getValue())->getId()
        );
    }

    /**
     * @param Dimension $dimension
     * @return void
     */
    private function commitChanges(Dimension $dimension)
    {
        if ($this->getCommitMode($dimension) == IndexationMode::MODE_FINAL) {
            $this->adapter->allowCommit();
            $this->adapter->commit();
        }
    }

    /**
     * @param Dimension $dimension
     * @return void
     */
    private function setCommitMode(Dimension $dimension)
    {
        if ($this->getCommitMode($dimension) != IndexationMode::MODE_PARTIAL) {
            $this->adapter->holdCommit();
        } else {
            $this->adapter->allowCommit();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $storeId = $this->getStoreIdByDimension($dimension);
        $this->setCommitMode($dimension);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $storeId);
            $this->adapter->addDocs($docs);
        }
        $this->commitChanges($dimension);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $dimension = reset($dimensions);
        $storeId = $this->getStoreIdByDimension($dimension);
        $this->setCommitMode($dimension);

        $queries = [];
        $uniqueKey = $this->adapter->getUniqueKey();
        foreach ($documents as $entityId) {
            $queries[] = $uniqueKey . ':' . $entityId . '|' . $storeId;
        }

        $this->adapter->deleteDocs($queries);
        $this->commitChanges($dimension);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->adapter->deleteDocs(['store_id:' . $this->scopeResolver->getScope($dimensions[0]->getValue())->getId()]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return $this->adapter->ping();
    }

    /**
     * @param Dimension $dimension
     * @return int
     */
    private function getStoreIdByDimension($dimension)
    {
        return $dimension->getName() == self::SCOPE_FIELD_NAME
            ? $this->scopeResolver->getScope($dimension->getValue())->getId() : Store::DEFAULT_STORE_ID;
    }
}
