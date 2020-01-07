<?php
namespace Magento\Analytics\Api;

/**
 * Provides link to file with collected report data.
 */
interface LinkProviderInterface
{
    /**
     * @return \Magento\Analytics\Api\Data\LinkInterface
     */
    public function get();
}
