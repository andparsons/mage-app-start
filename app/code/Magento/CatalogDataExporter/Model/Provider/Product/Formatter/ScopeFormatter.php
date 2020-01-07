<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product\Formatter;

use Magento\Framework\App\ResourceConnection;

/**
 * Class ScopeFormatter
 */
class ScopeFormatter implements FormatterInterface
{
    /**
     * @var array
     */
    private $scopes;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ScopeFormatter constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get resource table
     *
     * @param string $tableName
     * @return string
     */
    private function getTable(string $tableName) : string
    {
        return $this->resourceConnection->getTableName($tableName);
    }

    /**
     * Get scopes
     *
     * @return array
     */
    private function getScopes()
    {
        if (!$this->scopes) {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(['s' => $this->getTable('store')], [])
                ->join(
                    ['g' => $this->getTable('store_group')],
                    'g.group_id = s.group_id',
                    []
                )
                ->join(
                    ['w' => $this->getTable('store_website')],
                    'w.website_id = s.website_id',
                    []
                )
                ->columns(
                    [
                        'storeViewCode' => 's.code',
                        'storeCode' => 'g.code',
                        'websiteCode' => 'w.code'
                    ]
                )
                ->where('s.store_id != 0');
            $this->scopes = $connection->fetchAssoc($select);
        }
        return $this->scopes;
    }

    /**
     * Format data
     *
     * @param array $row
     * @return array
     */
    public function format(array $row): array
    {
        return array_merge($row, $this->getScopes()[$row['storeViewCode']]);
    }
}
