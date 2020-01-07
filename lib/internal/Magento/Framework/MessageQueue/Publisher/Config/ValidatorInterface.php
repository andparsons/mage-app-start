<?php
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Publisher config data validator.
 */
interface ValidatorInterface
{
    /**
     * Validate publisher config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     */
    public function validate($configData);
}
