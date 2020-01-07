<?php
namespace Magento\AdvancedSearch\Model\Client;

/**
 * @api
 * @since 100.1.0
 */
interface ClientInterface
{
    /**
     * Validate connection params for search engine
     *
     * @return bool
     * @since 100.1.0
     */
    public function testConnection();
}
