<?php
namespace Magento\Solr\Model\Client;

use Magento\AdvancedSearch\Model\Client\ClientInterface;

/**
 * Solarium client
 */
class Solarium implements ClientInterface
{
    /**
     * Solarium Client instance
     *
     * @var \Solarium\Client
     */
    protected $_client = '';

    /**
     * Initialize Solarium Client
     *
     * @param array $options
     * @param \Solarium\Client|null $solariumClient
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        $options = [],
        $solariumClient = null
    ) {
        $_optionsNames = ['hostname', 'login', 'password', 'port', 'path'];
        if (!sizeof(array_intersect($_optionsNames, array_keys($options)))) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
        if (!($solariumClient instanceof \Solarium\Client)) {
            $solariumClient = new \Solarium\Client();
        }

        $options['path'] = '/' . ltrim($options['path'], '/');
        $this->_client = $solariumClient;
        $this->_client->getEndpoint()
            ->setHost($options['hostname'])
            ->setPort($options['port'])
            ->setPath($options['path'])
            ->setAuthentication($options['login'], $options['password']);
        $solariumClient->getPlugin('postbigrequest');
        if (isset($options['timeout'])) {
            $this->_client->getEndpoint()->setTimeout($options['timeout']);
        }
    }

    /**
     * @return \Solarium\QueryType\Ping\Result
     */
    public function ping()
    {
        $query = $this->_client->createPing();
        return $this->_client->ping($query)->getData();
    }

    /**
     * Execute search by $query
     *
     * @param \Solarium\QueryType\Select\Query\Query $query
     * @return \Solarium\Core\Query\Result\ResultInterface
     */
    public function query(\Solarium\QueryType\Select\Query\Query $query)
    {
        return $this->_client->execute($query);
    }

    /**
     * Create a delete document based on a multiple queries and submit it
     *
     * @param array $rawQueries
     * @return \Solarium\Core\Client\Response
     */
    public function deleteByQueries($rawQueries)
    {
        /** @var \Solarium\QueryType\Update\Query\Query $update */
        $update = $this->_client->createUpdate();

        $update->addDeleteQueries($rawQueries);

        /** @var  \Solarium\QueryType\Update\Result $result */
        $result = $this->_client->update($update);

        return $result->getResponse();
    }

    /**
     *  Adds a collection of \Solarium\QueryType\Update\Query\Document\Document instances to the index
     *
     * @param array $docs
     * @return \Solarium\Core\Client\Response
     */
    public function addDocuments($docs)
    {
        /** @var \Solarium\QueryType\Update\Query\Query $update */
        $update = $this->_client->createUpdate();

        $update->addDocuments($docs);

        /** @var  \Solarium\QueryType\Update\Result $result */
        $result = $this->_client->update($update);

        return $result->getResponse();
    }

    /**
     * Defragments the index
     *
     * @return \Solarium\QueryType\Update\Result
     */
    public function optimize()
    {
        $query = $this->_client->createUpdate();
        $query->addOptimize(true, true, 5);
        return $this->_client->update($query);
    }

    /**
     * Finalizes all add/deletes made to the index
     *
     * @return void
     */
    public function commit()
    {
        $query = $this->_client->createUpdate();
        $query->addCommit();
        $this->_client->update($query);
    }

    /**
     * Withdraws any uncommitted changes
     *
     * @return \Solarium\QueryType\Update\Result
     */
    public function rollback()
    {
        $query = $this->_client->createUpdate();
        $query->addRollback();
        return $this->_client->update($query);
    }

    /**
     * Performs a simple ping to validate connection params
     *
     * @return bool
     */
    public function testConnection()
    {
        $this->ping();
        return true;
    }
}
