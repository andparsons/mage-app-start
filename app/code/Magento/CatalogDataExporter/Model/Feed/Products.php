<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Feed;

use Magento\DataExporter\Model\FeedInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Products
 *
 * @package Magento\CatalogDataExporter\Model\Feed
 */
class Products implements FeedInterface
{
    /**
     * Offset
     *
     * @var int
     */
    private static $offset = 1000;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Products constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
    }

    /**
     * @param string $timestamp
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getFeedSince(string $timestamp): array
    {
        $connection = $this->resourceConnection->getConnection();
        $recentTimestamp = null;


        $limit = $connection->fetchOne(
            $connection->select()
                ->from(
                    ['t' => $this->resourceConnection->getTableName('catalog_data_exporter_products')],
                    [ 'modified_at']
                )
                ->where('t.modified_at > ?', $timestamp)
                ->order('modified_at')
                ->limit(1, self::$offset)
        );
        $select = $connection->select()
            ->from(
                ['t' => $this->resourceConnection->getTableName('catalog_data_exporter_products')],
                [
                    'feed_data',
                    'modified_at',
                    'is_deleted'
                ]
            )
            ->where('t.modified_at > ?', $timestamp);
        if ($limit) {
            $select->where('t.modified_at <= ?', $limit);
        }
        $cursor = $connection->query($select);
        $output = [];
        while ($row = $cursor->fetch()) {
            $dataRow = $this->serializer->unserialize($row['feed_data']);
            $dataRow['modifiedAt'] = $row['modified_at'];
            $dataRow['deleted'] = (bool) $row['is_deleted'];
            $output[] = $dataRow;
            if ($recentTimestamp == null || $recentTimestamp < $row['modified_at']) {
                $recentTimestamp = $row['modified_at'];
            }
        }
        return [
            'recentTimestamp' => $recentTimestamp,
            'feed' => $output
        ];
    }
}
