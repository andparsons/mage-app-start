<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Render HTML DOM elements
 *
 * @api
 */
interface ElementRendererInterface
{
    /**
     * Render an element with attributes and optional children
     *
     * @param string $tagName
     * @param array $attributes
     * @param string $childrenHtml
     *
     * @return string
     */
    public function render(string $tagName, array $attributes, string $childrenHtml = '') : string;
}
