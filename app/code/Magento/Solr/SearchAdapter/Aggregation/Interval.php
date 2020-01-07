<?php
namespace Magento\Solr\SearchAdapter\Aggregation;

use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\Solr\SearchAdapter\ConnectionManager;
use Solarium\QueryType\Select\Query\Query;

class Interval implements IntervalInterface
{
    /**
     * Minimal possible value
     */
    const DELTA = 0.005;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @param Query $query
     * @param ConnectionManager $connectionManager
     * @param string $fieldName
     */
    public function __construct(Query $query, ConnectionManager $connectionManager, $fieldName)
    {
        $this->query = $query;
        $this->fieldName = $fieldName;
        $this->connectionManager = $connectionManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $query = clone $this->query;
        $query->setFields([$this->fieldName]);
        if ($lower !== null || $upper !== null) {
            $query->createFilterQuery($this->fieldName . '_range')
                ->setQuery(
                    '%1%:[%2% TO %3%]',
                    [
                        $this->fieldName,
                        $lower ? $lower - self::DELTA : '*',
                        $upper ? $upper - self::DELTA : '*',
                    ]
                );
        }
        $query->addSort($this->fieldName, Query::SORT_ASC)
            ->setStart($offset)
            ->setRows($limit);

        $resultSet = $this->connectionManager->getConnection()
            ->query($query);

        return $this->arrayValuesToFloat($resultSet);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function loadPrevious($data, $index, $lower = null)
    {
        $query = clone $this->query;
        $query->setFields([$this->fieldName]);
        $query->createFilterQuery($this->fieldName . '_less_than')
            ->setQuery('%1%:[* TO %2%]', [$this->fieldName, $data - self::DELTA]);
        if ($lower !== null) {
            $query->createFilterQuery($this->fieldName . '_greater_than')
                ->setQuery('%1%:[%2% TO *]', [$this->fieldName, $lower - self::DELTA]);
        }

        $stats = $query->getStats();
        $stats->createField($this->fieldName);

        $resultSet = $this->connectionManager->getConnection()
            ->query($query);

        /** @var \Solarium\QueryType\Select\Result\Stats\Stats $statsResult */
        $statsResult = $resultSet->getStats();
        $statsField = $statsResult->getResult($this->fieldName);

        if ($statsField === null || !$statsField->getCount()) {
            return false;
        }

        $offset = $statsField->getCount();

        return $this->load($index - $offset + 1, $offset - 1, $lower);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        $query = clone $this->query;
        $query->setFields([$this->fieldName]);
        $query->createFilterQuery($this->fieldName . '_greater_than')
            ->setQuery('%1%:[%2% TO *]', [$this->fieldName, $data + self::DELTA]);
        if ($upper !== null) {
            $query->createFilterQuery($this->fieldName . '_less_than')
                ->setQuery('%1%:[* TO %2%]', [$this->fieldName, $upper - self::DELTA]);
        }

        $stats = $query->getStats();
        $stats->createField($this->fieldName);

        $resultSet = $this->connectionManager->getConnection()
            ->query($query);

        /** @var \Solarium\QueryType\Select\Result\Stats\Stats $statsResult */
        $statsResult = $resultSet->getStats();
        $statsField = $statsResult->getResult($this->fieldName);

        if ($statsField === null || !$statsField->getCount()) {
            return false;
        }
        $offset = $statsField->getCount();

        $query->addSort($this->fieldName, Query::SORT_ASC)
            ->setStart($offset - 1)
            ->setRows($rightIndex - $offset + 1);

        $resultSet = $this->connectionManager->getConnection()
            ->query($query);

        return array_reverse($this->arrayValuesToFloat($resultSet));
    }

    /**
     * @param array $documents
     * @return float[]
     */
    private function arrayValuesToFloat($documents)
    {
        $returnPrices = [];
        foreach ($documents as $document) {
            $returnPrices[] = (float) $document[$this->fieldName];
        }

        return $returnPrices;
    }
}
