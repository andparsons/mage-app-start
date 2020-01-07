<?php
namespace Magento\Solr\SearchAdapter;

/**
 * Factory class for @see \Solarium\QueryType\Select\Query\Query
 */
class QueryFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Solarium\QueryType\Select\Query\Query::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return \Solarium\QueryType\Select\Query\Query
     */
    public function create()
    {
        return new $this->_instanceName();
    }
}
