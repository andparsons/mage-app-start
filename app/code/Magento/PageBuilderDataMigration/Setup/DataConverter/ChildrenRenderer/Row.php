<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenRenderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\ChildrenRendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer\Column;

/**
 * Children renderer which handles wrapping columns in column groups
 */
class Row implements ChildrenRendererInterface
{
    /**
     * @inheritdoc
     */
    public function render(array $children, $renderChildCallback) : string
    {
        $childHtml = '';
        $columns = [];
        foreach ($children as $childIndex => $childItem) {
            if (isset($childItem['type']) && $childItem['type'] === 'column') {
                $columns[] = $childItem;
            } else {
                // If there's mixed content in the row we need to render the columns before the next item
                if (count($columns) > 0) {
                    $childHtml .= $this->renderColumnGroup($columns, $childIndex, $renderChildCallback);
                    $columns = [];
                }
                $childHtml .= $renderChildCallback($childItem, $childIndex);
            }
        }

        // If we have additional columns to process append them
        if (count($columns) > 0) {
            $childHtml .= $this->renderColumnGroup($columns, 0, $renderChildCallback);
        }

        return $childHtml;
    }

    /**
     * Wrap columns in a column group
     *
     * @param array $columns
     * @param number $childIndex
     * @param \Closure $renderChildCallback
     *
     * @return string
     */
    private function renderColumnGroup(array $columns, $childIndex, $renderChildCallback)
    {
        $childHtml = '';

        $currentTotalWidth = 0;
        $columnsInGroup = [];
        foreach ($columns as $index => $column) {
            $currentTotalWidth += $this->getColumnWidth($column);
            // Handle some columns not totalling 1, such as 1/3
            if ($currentTotalWidth >= 0.99 && $currentTotalWidth <= 1.01) {
                $currentTotalWidth = 1;
            }
            $columnsInGroup[] = $column;

            // Determine if the next column will wrap
            if ($this->willNextColumnWrap($currentTotalWidth, $columns, $index)) {
                $remainingWidth = 1 - $currentTotalWidth;
                $columnsInGroup[] = [
                    'type' => 'column',
                    'formData' => [
                        'width' => $remainingWidth
                    ]
                ];
                $currentTotalWidth += $remainingWidth;
            }

            // When we have a whole row, wrap it within a column group
            if ($currentTotalWidth == 1) {
                $childHtml .= $renderChildCallback(['type' => 'column_group'], $childIndex, $columnsInGroup);
                $currentTotalWidth = 0;
                $columnsInGroup = [];
            }
        }

        return $childHtml;
    }

    /**
     * Check to see if the next columns width would cause itself to wrap, meaning we need to insert an empty
     * column to ensure the total width of this group is 100%. Also check if we're the last column in the
     * current set and the total width is below 1, then we need to add an empty column.
     *
     * @param number $currentTotalWidth
     * @param array $columns
     * @param number $index
     *
     * @return bool
     */
    private function willNextColumnWrap($currentTotalWidth, array $columns, $index) : bool
    {
        return ($currentTotalWidth < 1
            && (isset($columns[$index + 1])
                && $currentTotalWidth + $this->getColumnWidth($columns[$index + 1]) > 1.01)
            || (count($columns) - 1 === $index && $currentTotalWidth < 1)
        );
    }

    /**
     * Retrieve the column width
     *
     * @param array $column
     *
     * @return float
     */
    private function getColumnWidth(array $column)
    {
        if (!isset($column['formData']['width'])) {
            return 0;
        }

        return $column['formData']['width'];
    }
}
