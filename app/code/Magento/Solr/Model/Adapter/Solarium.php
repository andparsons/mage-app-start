<?php
namespace Magento\Solr\Model\Adapter;

/**
 * Solr search engine adapter that perform raw queries to Solr server based on solr client library
 * and basic solr adapter
 */
class Solarium extends \Magento\Solr\Model\Adapter\AbstractAdapter
{
    /**
     * Create a delete document based on a multiple queries and submit it
     *
     * @param array $rawQueries Expected to be utf-8 encoded
     * @param bool $fromPending
     * @param bool $fromCommitted
     * @param int $timeout Maximum expected duration of the delete operation on the server
     * (otherwise, will throw a communication exception)
     *
     * @throws \Exception If an error occurs during the service call
     * @return \Solarium\Core\Client\Response
     */
    public function deleteByQueries($rawQueries, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        $pendingValue = $fromPending ? 'true' : 'false';
        $committedValue = $fromCommitted ? 'true' : 'false';
        return $this->client->deleteByQueries($rawQueries, $pendingValue, $committedValue, $timeout);
    }

    /**
     * Add documents to Solr index
     *
     * @param array $docs
     * @param bool $overwrite
     * @param int $commitWithin
     *
     * @return \Solarium\Core\Client\Response
     */
    public function addDocuments($docs, $overwrite = true, $commitWithin = 0)
    {
        return $this->client->addDocuments($docs, $overwrite, $commitWithin);
    }

    /**
     * Defragments the index
     *
     * @return \Solarium\QueryType\Update\Result
     */
    public function optimize()
    {
        return $this->client->optimize();
    }
}
