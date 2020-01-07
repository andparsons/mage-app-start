<?php

namespace Magento\Framework\View\Asset;

/**
 * An abstraction for getting context path of an asset
 */
interface ContextInterface
{
    /**
     * Get context path of an asset
     *
     * @return string
     */
    public function getPath();

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl();
}
