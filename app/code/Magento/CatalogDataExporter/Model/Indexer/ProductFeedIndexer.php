<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Indexer;

use Magento\CatalogDataExporter\Model\Query\DeletedProductsQuery;
use Magento\DataExporter\Export\Processor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ProductFeedIndexer
 */
class ProductFeedIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * Batch size
     *
     * @var int
     */
    private static $batchSize = 1000;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var DeletedProductsQuery
     */
    private $deletedProductsQuery;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ProductFeedIndexer constructor.
     *
     * @param Processor $processor
     * @param ResourceConnection $resourceConnection
     * @param SerializerInterface $serializer
     * @param DeletedProductsQuery $deletedProductsQuery
     */
    public function __construct(
        Processor $processor,
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer,
        DeletedProductsQuery $deletedProductsQuery
    ) {
        $this->processor = $processor;
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        $this->deletedProductsQuery = $deletedProductsQuery;
    }

    /**
     * Get all product IDs
     *
     * @return \Generator
     * @throws \Zend_Db_Statement_Exception
     */
    private function getAllIds()
    {
        $connection = $this->resourceConnection->getConnection();
        $lastKnownId = 0;
        $continueReindex = true;
        while ($continueReindex) {
            $ids = $connection->fetchAll(
                $connection->select()
                    ->from(
                        ['p' => 'catalog_product_entity'],
                        ['productId' => 'p.entity_id']
                    )
                ->where('entity_id > ?', $lastKnownId)
                ->order('entity_id')
                ->limit(self::$batchSize)
            );
            if (empty($ids)) {
                $continueReindex = false;
            } else {
                yield $ids;
                $lastKnownId = end($ids)['productId'];
            }
        }
    }

    /**
     * Execute full indexation
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function executeFull()
    {
        $this->deletedProductsQuery->updateDeletedFlagQuery();
        foreach ($this->getAllIds() as $ids) {
            $this->process($ids);
        }
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $arguments = [];
        foreach ($ids as $id) {
            $arguments[] = ['productId' => $id];
        }
        $this->deletedProductsQuery->updateDeletedFlagQuery();
        $this->process($arguments);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->deletedProductsQuery->updateDeletedFlagQuery();
        $this->process([['productId' => $id]]);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids)
    {
        $arguments = [];
        foreach ($ids as $id) {
            $arguments[] = ['productId' => $id];
        }
        $this->deletedProductsQuery->updateDeletedFlagQuery();
        $this->process($arguments);
    }

    /**
     * Data formatter
     *
     * @param array $data
     * @return array
     */
    private function formatData(array $data): array
    {
        $output = [];
        foreach ($data as $row) {
            $output[] = [
                'sku' => $row['sku'],
                'store_view_code' => $row['storeViewCode'],
                'feed_data' => $this->serializer->serialize($row)
            ];
        }
        return $output;
    }

    /**
     * Indexer feed data processor
     *
     * @param array $ids
     */
    private function process($ids = [])
    {
        $data = $this->processor->process('products', $ids);
        $chunks = array_chunk($data, self::$batchSize);
        $connection = $this->resourceConnection->getConnection();
        foreach ($chunks as $chunk) {
            $connection->insertOnDuplicate(
                $this->resourceConnection->getTableName('catalog_data_exporter_products'),
                $this->formatData($chunk),
                ['feed_data', 'is_deleted']
            );
        }
    }
}
