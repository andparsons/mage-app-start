<?php
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\Bulk\ExchangeInterface
 *
 * @api
 * @since 102.0.3
 */
interface ExchangeFactoryInterface
{
    /**
     * Create exchange instance.
     *
     * @param string $connectionName
     * @param array $data
     * @return ExchangeInterface
     * @throws \LogicException If exchange is not defined for the specified connection type
     *                          or it doesn't implement ExchangeInterface
     * @since 102.0.3
     */
    public function create($connectionName, array $data = []);
}
