<?php
declare(strict_types=1);

namespace Magento\ServicesId\Model;

/**
 * Interface for SaaS Services configuration values
 *
 * @api
 */
interface ServicesConfigInterface
{
    /**
     * Get Instance ID for SaaS Services
     *
     * @return string|null
     */
    public function getInstanceId();

    /**
     * Get Environment for SaaS Services
     *
     * @return string|null
     */
    public function getEnvironment();

    /**
     * Get Environment ID for SaaS Services
     *
     * @return string|null
     */
    public function getEnvironmentId();
}
