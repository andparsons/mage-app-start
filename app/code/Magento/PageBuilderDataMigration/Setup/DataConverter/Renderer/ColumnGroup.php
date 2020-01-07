<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;

/**
 * Render column group to PageBuilder format
 */
class ColumnGroup implements RendererInterface
{
    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @param ElementRendererInterface $elementRenderer
     */
    public function __construct(
        ElementRendererInterface $elementRenderer
    ) {
        $this->elementRenderer = $elementRenderer;
    }

    /**
     * @inheritdoc
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $rootElementAttributes = [
            'data-element' => 'main',
            'class' => 'pagebuilder-column-group',
            'style' => 'display: flex;',
            'data-content-type' => 'column-group',
            'data-appearance' => 'default',
            'data-grid-size' => '12'
        ];

        return $this->elementRenderer->render(
            'div',
            $rootElementAttributes,
            isset($additionalData['children']) ? $additionalData['children'] : ''
        );
    }
}
