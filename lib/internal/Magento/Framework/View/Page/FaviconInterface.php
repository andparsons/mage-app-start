<?php
namespace Magento\Framework\View\Page;

/**
 * Favicon interface
 *
 * @api
 * @since 100.0.2
 */
interface FaviconInterface
{
    /**
     * @return string
     */
    public function getFaviconFile();

    /**
     * @return string
     */
    public function getDefaultFavicon();
}
