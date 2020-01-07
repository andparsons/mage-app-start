<?php
declare(strict_types=1);
namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Render children for current content type
 *
 * @api
 */
interface ChildrenRendererInterface
{
    /**
     * Render children for element
     *
     * @param array $children
     * @param \Closure $renderChildCallback
     *
     * @return string
     */
    public function render(array $children, $renderChildCallback) : string;
}
