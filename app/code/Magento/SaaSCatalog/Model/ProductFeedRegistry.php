<?php
declare(strict_types=1);

namespace Magento\SaaSCatalog\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class FeedFilter
 */
class ProductFeedRegistry
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $excludeFields;

    /**
     * FeedRegistry constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SerializerInterface $serializer
     * @param array $excludeFields
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer,
        array $excludeFields = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        $this->excludeFields = $excludeFields;
    }

    /**
     * Get row identifier
     *
     * @param array $row
     * @return string
     */
    private function getIdentifier(array $row) : string
    {
        return sha1(
            $this->serializer->serialize(
                ['sku' => $row['sku'], 'storeViewCode' => $row['storeViewCode']]
            )
        );
    }

    /**
     * Sanitize row
     *
     * @param $row
     * @return array
     */
    private function sanitizeRow($row) : array
    {
        $output = [];
        foreach ($row as $key => $value) {
            if (!in_array($key, $this->excludeFields)) {
                $output[$key] = $value;
            }
        }
        return $output;
    }

    /**
     * Hash row data
     *
     * @param array $row
     * @return string
     */
    private function hashData(array $row) : string
    {
        return sha1($this->serializer->serialize($this->sanitizeRow($row)));
    }

    /**
     * Register feed
     *
     * @param array $data
     */
    public function registerFeed(array $data) : void
    {
        $input = [];
        $connection = $this->resourceConnection->getConnection();
        foreach ($data as $row) {
            $identifier = $this->getIdentifier($row);
            $input[$identifier] = [
                'identifier' => $identifier,
                'feed_hash' => $this->hashData($row)
            ];
        }
        $connection->insertOnDuplicate(
            $this->resourceConnection->getTableName('catalog_data_submitted_hash'),
            $input,
            ['feed_hash']
        );
    }

    /**
     * Filter data
     *
     * @param array $data
     * @return array
     */
    public function filter(array $data) : array
    {
        $identifiers = [];
        $output = [];
        foreach ($data as $row) {
            $identifier = $this->getIdentifier($row);
            $identifiers[$identifier] = $identifier;
        }
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['f' => $this->resourceConnection->getTableName('catalog_data_submitted_hash')])
            ->where('f.identifier IN (?)', $identifiers);
        $hashes = $connection->fetchAssoc($select);
        foreach ($data as $row) {
            $identifier = $this->getIdentifier($row);
            if (isset($hashes[$identifier]) && $this->hashData($row) == $hashes[$identifier]['feed_hash']) {
                continue;
            }
            $output[] = $row;
        }
        return $output;
    }
}
