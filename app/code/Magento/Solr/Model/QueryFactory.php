<?php
namespace Magento\Solr\Model;

class QueryFactory
{
    /**
     * @var string
     */
    private $instanceName;

    /**
     * @param string $instanceName
     */
    public function __construct(
        $instanceName = \Solarium\QueryType\Select\Query\Query::class
    ) {
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return \Solarium\QueryType\Select\Query\Query
     */
    public function create()
    {
        return new $this->instanceName();
    }
}
