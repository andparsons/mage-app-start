<?php

namespace Magento\Framework\App\DeploymentConfig\Writer;

/**
 * Interface \Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface
 *
 */
interface FormatterInterface
{
    /**
     * Format deployment configuration
     *
     * @param array $data
     * @param array $comments
     * @return string
     */
    public function format($data, array $comments = []);
}
