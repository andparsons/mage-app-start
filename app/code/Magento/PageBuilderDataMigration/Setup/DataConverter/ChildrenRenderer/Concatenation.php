<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenRenderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenRendererInterface;

/**
 * Children renderer which concatenates all children output together
 */
class Concatenation implements ChildrenRendererInterface
{
    /**
     * @inheritdoc
     */
    public function render(array $children, $renderChildCallback) : string
    {
        $childHtml = '';
        foreach ($children as $childIndex => $childItem) {
            $childHtml .= $renderChildCallback($childItem, $childIndex);
        }
        return $childHtml;
    }
}
