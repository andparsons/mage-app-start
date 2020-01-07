<?php
namespace Magento\Solr\Model\Adapter;

use Magento\Framework\Exception\LocalizedException;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Solr\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\AdvancedSearch\Model\ResourceModel\Index;
use Psr\Log\LoggerInterface;

/**
 * Search engine abstract adapter
 *
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractAdapter
{
    /**
     * Field to use to determine and enforce document uniqueness
     */
    const UNIQUE_KEY = 'unique';

    /**
     * @var Index
     */
    protected $resourceIndex;

    /**
     * Store Solr Client instance
     *
     * @var object
     */
    protected $client = null;

    /**
     * Define if automatic commit on changes for adapter is allowed
     *
     * @var bool
     */
    protected $holdCommit = false;

    /**
     * Define if search engine index needs optimization
     *
     * @var bool
     */
    protected $indexNeedsOptimization = false;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AttributeContainer
     */
    private $attributeContainer;

    /**
     * @var DocumentDataMapper
     */
    private $documentDataMapper;

    /**
     * @var float|bool
     */
    private $ping;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var ClientOptionsInterface
     */
    private $clientHelper;

    /**
     * @param Index $resourceIndex
     * @param LoggerInterface $logger
     * @param AttributeContainer $attributeContainer
     * @param DocumentDataMapper $documentDataMapper
     * @param ClientFactoryInterface $clientFactory
     * @param ClientOptionsInterface $clientHelper
     * @param array $options
     * @throws LocalizedException
     */
    public function __construct(
        Index $resourceIndex,
        LoggerInterface $logger,
        AttributeContainer $attributeContainer,
        DocumentDataMapper $documentDataMapper,
        ClientFactoryInterface $clientFactory,
        ClientOptionsInterface $clientHelper,
        $options = []
    ) {
        $this->clientFactory = $clientFactory;
        $this->clientHelper = $clientHelper;
        $this->resourceIndex = $resourceIndex;
        $this->logger = $logger;
        $this->attributeContainer = $attributeContainer;
        $this->documentDataMapper = $documentDataMapper;

        try {
            $this->connect($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Connect to Search Engine Client by specified options.
     * Should initialize _client
     *
     * @param array $options
     * @return \Magento\Solr\Model\Client\Solarium
     */
    protected function connect($options = [])
    {
        try {
            $this->client = $this->clientFactory->create($this->clientHelper->prepareClientOptions($options));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \RuntimeException('Solr client is not set.');
        }

        return $this->client;
    }

    /**
     * Create Solr Input Documents by specified data
     *
     * @param array $docData
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareDocsPerStore(array $docData, $storeId)
    {
        if (0 === count($docData)) {
            return [];
        }

        $docs = [];

        $productIds = array_keys($docData);

        $priceIndexData = $this->attributeContainer->getSearchableAttribute('price')
            ? $this->resourceIndex->getPriceIndexData($productIds, $storeId)
            : [];

        $categoryIndexData = $this->resourceIndex->getCategoryProductIndexData($storeId, $productIds);

        foreach ($docData as $productId => $productIndexData) {
            if (!$this->isAvailableInIndex($productIndexData, $productId)) {
                continue;
            }

            $document = $this->documentDataMapper->map(
                $productIndexData,
                $productId,
                $storeId,
                $priceIndexData,
                $categoryIndexData
            );

            $docs[] = $document;
        }

        return $docs;
    }

    /**
     * Is data available in index
     *
     * @param array $productIndexData
     * @param int $productId
     * @return bool
     */
    protected function isAvailableInIndex(array $productIndexData, $productId)
    {
        if (!is_array($productIndexData) || !count($productIndexData)) {
            return false;
        }

        $visibilityId = $this->attributeContainer->getAttributeIdByCode('visibility');

        if (!isset($productIndexData[$visibilityId][$productId])) {
            return false;
        }

        return true;
    }

    /**
     * Add prepared Solr Input documents to Solr index
     *
     * @param array $docs
     * @return $this
     * @throws \Exception
     */
    public function addDocs(array $docs)
    {
        if (count($docs)) {
            try {
                $this->client->addDocuments($docs);
                $this->commit();
            } catch (\Exception $e) {
                $this->rollback();
                $this->logger->critical($e);
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Finalizes all add/deletes made to the index
     *
     * @return object|false
     */
    public function commit()
    {
        if ($this->holdCommit) {
            return false;
        }

        $this->beforeCommit();
        $result = $this->client->commit();
        $this->afterCommit();

        return $result;
    }

    /**
     * Rollbacks all add/deletes made to the index since the last commit
     *
     * @return object
     */
    public function rollback()
    {
        return $this->client->rollback();
    }

    /**
     * Before commit action
     *
     * @return $this
     */
    protected function beforeCommit()
    {
        return $this;
    }

    /**
     * After commit action
     *
     * @return $this
     */
    protected function afterCommit()
    {
        $this->indexNeedsOptimization = true;

        return $this;
    }

    /**
     * Remove documents from Solr index
     *
     * @param array $queries
     * @return $this
     * @throws \Exception
     */
    public function deleteDocs(array $queries = [])
    {
        if (count($queries)) {
            try {
                $this->client->deleteByQueries($queries);
                $this->commit();
            } catch (\Exception $e) {
                $this->rollback();
                $this->logger->critical($e);
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Perform optimize operation
     * Same as commit operation, but also defragment the index for faster search performance
     *
     * @return object|false
     */
    public function optimize()
    {
        if ($this->holdCommit) {
            return false;
        }

        $this->beforeOptimize();
        $result = $this->client->optimize();
        $this->afterOptimize();

        return $result;
    }

    /**
     * Before optimize action.
     * _beforeCommit method is called because optimize includes commit in itself
     *
     * @return $this
     */
    protected function beforeOptimize()
    {
        $this->beforeCommit();

        return $this;
    }

    /**
     * After commit action
     * _afterCommit method is called because optimize includes commit in itself
     *
     * @return $this
     */
    protected function afterOptimize()
    {
        $this->afterCommit();

        $this->indexNeedsOptimization = false;

        return $this;
    }

    /**
     * Getter for field to use to determine and enforce document uniqueness
     *
     * @return string
     */
    public function getUniqueKey()
    {
        return self::UNIQUE_KEY;
    }

    /**
     * Retrieve Solr server status
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function ping()
    {
        try {
            $this->ping = $this->client->ping();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not ping search engine: %1', $e->getMessage())
            );
        }
        return true;
    }

    /**
     * Hold commit of changes for adapter
     *
     * @return $this
     */
    public function holdCommit()
    {
        $this->holdCommit = true;

        return $this;
    }

    /**
     * Allow changes commit for adapter
     *
     * @return $this
     */
    public function allowCommit()
    {
        $this->holdCommit = false;

        return $this;
    }

    /**
     * Check if third party search engine index needs optimization
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIndexNeedsOptimization()
    {
        return $this->indexNeedsOptimization;
    }

    /**
     * Define if third party search engine index needs optimization
     *
     * @param bool $state
     * @return $this
     */
    public function setIndexNeedsOptimization($state = true)
    {
        $this->indexNeedsOptimization = (bool)$state;

        return $this;
    }
}
