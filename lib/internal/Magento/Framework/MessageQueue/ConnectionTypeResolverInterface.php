<?php
namespace Magento\Framework\MessageQueue;

/**
 * Message Queue connection type resolver.
 */
interface ConnectionTypeResolverInterface
{
    /**
     * Get connection type by connection name.
     *
     * @param string $connectionName
     * @return string|null
     */
    public function getConnectionType($connectionName);
}
