<?php
namespace Magento\Solr\SearchAdapter;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Solr\SearchAdapter;
use Magento\Solr\SearchAdapter\Aggregation\Builder;

/**
 * SOLR Search Adapter
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * Response Factory
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var Aggregation\StatisticsBuilder
     */
    private $statisticsBuilder;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var Builder
     */
    private $aggregationBuilder;

    /**
     * @param ConnectionManager $connectionManager
     * @param Mapper $mapper
     * @param SearchAdapter\ResponseFactory $responseFactory
     * @param Aggregation\StatisticsBuilder $statisticsBuilder
     * @param Builder $aggregationBuilder
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Mapper $mapper,
        SearchAdapter\ResponseFactory $responseFactory,
        Aggregation\StatisticsBuilder $statisticsBuilder,
        Builder $aggregationBuilder
    ) {
        $this->connectionManager = $connectionManager;
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->statisticsBuilder = $statisticsBuilder;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    /**
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        $client = $this->connectionManager->getConnection();
        $query = $this->mapper->buildQuery($request);
        $this->statisticsBuilder->build($request, $query);
        $rawResponse = $client->query($query);

        return $this->responseFactory->create([
            'documents' => $rawResponse,
            'aggregations' => $this->aggregationBuilder->build($request, $rawResponse)
        ]);
    }
}
