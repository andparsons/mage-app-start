<?php
namespace Magento\Framework\Search;

/**
 * Search Engine interface
 */
interface SearchEngineInterface
{
    /**
     * Process Search Request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function search(RequestInterface $request);
}
