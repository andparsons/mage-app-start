<?php
namespace Magento\Catalog\Block;

/**
 * Shortcut block interface
 *
 * @api
 * @since 100.0.2
 */
interface ShortcutInterface
{
    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias();
}