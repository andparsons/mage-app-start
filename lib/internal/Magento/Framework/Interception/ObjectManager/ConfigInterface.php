<?php
namespace Magento\Framework\Interception\ObjectManager;

/**
 * Interface \Magento\Framework\Interception\ObjectManager\ConfigInterface
 *
 */
interface ConfigInterface extends \Magento\Framework\ObjectManager\ConfigInterface
{
    /**
     * Set Interception config
     *
     * @param \Magento\Framework\Interception\ConfigInterface $interceptionConfig
     * @return void
     */
    public function setInterceptionConfig(\Magento\Framework\Interception\ConfigInterface $interceptionConfig);

    /**
     * Retrieve instance type without interception processing
     *
     * @param string $instanceName
     * @return string
     */
    public function getOriginalInstanceType($instanceName);
}
