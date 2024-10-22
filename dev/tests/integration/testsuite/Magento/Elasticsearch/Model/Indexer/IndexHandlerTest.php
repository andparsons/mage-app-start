<?php
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext as CatalogSearchFulltextIndexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch6\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Indexer\Model\Indexer;

/**
 * Important: Please make sure that each integration test file works with unique elastic search index. In order to
 * achieve this, use @magentoConfigFixture to pass unique value for 'elasticsearch_index_prefix' for every test
 * method. E.g. '@magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest'
 *
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Elasticsearch/_files/indexer.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ElasticsearchClient
     */
    private $client;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int[]
     */
    private $storeIds;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var SearchIndexNameResolver
     */
    private $searchIndexNameResolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $connectionManager = Bootstrap::getObjectManager()->create(ConnectionManager::class);
        $this->client = $connectionManager->getConnection();

        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
        $this->storeIds = array_keys($this->storeManager->getStores());

        $clientConfig = Bootstrap::getObjectManager()->create(Config::class);
        $this->entityType = $clientConfig->getEntityType();

        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->indexer->load(CatalogSearchFulltextIndexer::INDEXER_ID);
        $this->indexer->reindexAll();

        $this->searchIndexNameResolver = Bootstrap::getObjectManager()->create(SearchIndexNameResolver::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest
     * @return void
     */
    public function testReindexAll(): void
    {
        $productApple = $this->productRepository->get('fulltext-1');
        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('Apple', $storeId);
            $this->assertCount(1, $products);
            $this->assertEquals($productApple->getId(), $products[0]['_id']);

            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(5, $products);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest
     * @return void
     */
    public function testReindexRowAfterEdit(): void
    {
        $this->storeManager->setCurrentStore('admin');
        $productApple = $this->productRepository->get('fulltext-1');
        $productApple->setName('Simple Product Cucumber');
        $this->productRepository->save($productApple);

        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('Apple', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Cucumber', $storeId);
            $this->assertCount(1, $products);
            $this->assertEquals($productApple->getId(), $products[0]['_id']);

            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(5, $products);
        }
    }

    /**
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest
     * @return void
     */
    public function testReindexRowAfterMassAction(): void
    {
        $productApple = $this->productRepository->get('fulltext-1');
        $productBanana = $this->productRepository->get('fulltext-2');
        $productIds = [
            $productApple->getId(),
            $productBanana->getId(),
        ];
        $attrData = [
            'name' => 'Simple Product Common',
        ];
        /** @var ProductAction $action */
        $action = Bootstrap::getObjectManager()->get(ProductAction::class);

        foreach ($this->storeIds as $storeId) {
            $action->updateAttributes($productIds, $attrData, $storeId);

            $products = $this->searchByName('Apple', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Banana', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Unknown', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Common', $storeId);
            $this->assertCount(2, $products);

            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(5, $products);
        }
    }

    /**
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testReindexRowAfterDelete(): void
    {
        $productBanana = $this->productRepository->get('fulltext-2');
        $this->productRepository->delete($productBanana);

        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('Banana', $storeId);
            $this->assertEmpty($products);

            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(4, $products);
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest
     * @magentoDataFixture Magento/Elasticsearch/_files/configurable_products.php
     * @return void
     */
    public function testReindexRowAfterUpdateStockStatus(): void
    {
        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('ProductOption1', $storeId);
            $this->assertNotEmpty($products);
        }
        $product = $this->productRepository->get('simple_10');
        /** @var StockRegistryInterface $stockRegistry */
        $stockRegistry = Bootstrap::getObjectManager()->create(StockRegistryInterface::class);
        $stockItem = $stockRegistry->getStockItem($product->getId());
        $stockItem->setIsInStock(false);
        /** @var StockItemRepositoryInterface $stockRepository */
        $stockRepository = Bootstrap::getObjectManager()->create(StockItemRepositoryInterface::class);
        $stockRepository->save($stockItem);

        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('ProductOption1', $storeId);
            $this->assertEmpty($products);

            $products = $this->searchByName('Configurable', $storeId);
            $this->assertNotEmpty($products);
        }
    }

    /**
     * Search docs in Elasticsearch by name.
     *
     * @param string $text
     * @param int $storeId
     * @return array
     */
    private function searchByName(string $text, int $storeId): array
    {
        $index = $this->searchIndexNameResolver->getIndexName($storeId, $this->indexer->getId());
        $searchQuery = [
            'index' => $index,
            'type' => $this->entityType,
            'body' => [
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'should' => [
                            [
                                'match' => [
                                    'name' => $text,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->client->query($searchQuery);
        $products = isset($queryResult['hits']['hits']) ? $queryResult['hits']['hits'] : [];

        return $products;
    }
}
