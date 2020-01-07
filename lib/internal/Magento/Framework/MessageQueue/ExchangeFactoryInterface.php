<?php
namespace Magento\Framework\MessageQueue;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\ExchangeInterface
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
     * @since 102.0.3
     */
    public function create($connectionName, array $data = []);
}
