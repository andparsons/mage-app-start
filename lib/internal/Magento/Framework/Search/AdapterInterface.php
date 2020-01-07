<?php
namespace Magento\Framework\Search;

use Magento\Framework\Search\Response\QueryResponse;

/**
 * Search Adapter interface
 */
interface AdapterInterface
{
    /**
     * Process Search Request
     *
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request);
}
