<?php
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Topology config data validator.
 */
interface ValidatorInterface
{
    /**
     * Validate topology config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     */
    public function validate($configData);
}
