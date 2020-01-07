<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Render HTML DOM elements
 */
class ElementRenderer implements ElementRendererInterface
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
    public function render(string $tagName, array $attributes, string $childrenHtml = '') : string
    {
        $rootElementHtml = '<' . $tagName;
        foreach ($attributes as $attributeName => $attributeValue) {
            if ($attributeValue !== null && $attributeValue !== false) {
                $attributeValue = trim($attributeValue);
                if ($attributeValue !== null && $attributeValue !== false) {
                    $rootElementHtml .= " $attributeName=\"$attributeValue\"";
                }
            }
        }
        $rootElementHtml .= '>' . $childrenHtml . '</' . $tagName . '>';
        return $rootElementHtml;
    }
}
