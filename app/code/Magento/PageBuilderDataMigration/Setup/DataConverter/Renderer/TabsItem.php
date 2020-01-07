<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;

/**
 * Render tab item to PageBuilder format
 */
class TabsItem implements RendererInterface
{
    /**
     * @var StyleExtractorInterface
     */
    private $styleExtractor;

    /**
     * @var EavAttributeLoaderInterface
     */
    private $eavAttributeLoader;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $eavData = isset($itemData['entityId']) ? $this->eavAttributeLoader->load($itemData['entityId']) : [];

        $cssClasses = $eavData['css_classes'] ?? '';

        $tabChildIndex = $additionalData['childIndex'] ?? 0;

        $rootElementAttributes = [
            'data-element' => 'main',
            'data-content-type' => 'tab-item',
            'data-appearance' => 'default',
            'class' => $cssClasses,
            'data-tab-name' => $eavData['title'] ?? 'Tab ' . $tabChildIndex,
            'id' => 'tab' . $additionalData['parentChildIndex'] . '-' . $tabChildIndex
        ];

        $textAreaStyles = '';
        foreach ($this->getTextAdvancedDefaults() as $key => $value) {
            $textAreaStyles .= $key . ': ' . $value . '; ';
        }
        $textAreaStyles = rtrim($textAreaStyles, ' ');

        $formData = $itemData['formData'] ?? [];
        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );

        $rootElementHtml = '<div';
        foreach ($rootElementAttributes as $attributeName => $attributeValue) {
            $rootElementHtml .= $attributeValue ? " $attributeName=\"$attributeValue\"" : '';
        }
        $rootElementHtml .= '><div data-element="main" data-content-type="text" data-appearance="default"'
            . ' style="' . $textAreaStyles . '">'
            . ($eavData['textarea'] ?? '')
            . '</div></div>';

        return $rootElementHtml;
    }

    /**
     * Define the defaults for the advanced section
     *
     * @return array
     */
    private function getAdvancedDefaults()
    {
        return [
            'text-align' => '',
            'border-width' => '1px',
            'border-radius' => '0px',
            'padding' => '15px 0px',
        ];
    }

    /**
     * Define the defaults for the advanced section for the text content type inside the tab item
     *
     * @return array
     */
    private function getTextAdvancedDefaults()
    {
        return [
            'border-style' => 'none',
            'border-width' => '1px',
            'border-radius' => '0px'
        ];
    }
}
