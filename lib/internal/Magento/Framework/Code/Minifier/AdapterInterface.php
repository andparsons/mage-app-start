<?php

/**
 * Interface for minification adapters
 */
namespace Magento\Framework\Code\Minifier;

/**
 * Interface \Magento\Framework\Code\Minifier\AdapterInterface
 *
 */
interface AdapterInterface
{
    /**
     * Minify content
     *
     * @param string $content
     * @return string
     */
    public function minify($content);
}
