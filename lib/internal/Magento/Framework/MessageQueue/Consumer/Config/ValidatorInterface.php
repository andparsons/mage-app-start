<?php
namespace Magento\Framework\MessageQueue\Consumer\Config;

/**
 * Queue consumer config validator interface.
 */
interface ValidatorInterface
{
    /**
     * Validate merged consumer config data.
     *
     * @param array $configData
     * @return void
     * @throws \LogicException
     */
    public function validate($configData);
}
