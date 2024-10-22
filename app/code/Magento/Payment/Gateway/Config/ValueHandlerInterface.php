<?php
namespace Magento\Payment\Gateway\Config;

/**
 * Interface ValueHandlerInterface
 * @package Magento\Payment\Gateway\Config
 * @api
 * @since 100.0.2
 */
interface ValueHandlerInterface
{
    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null);
}
