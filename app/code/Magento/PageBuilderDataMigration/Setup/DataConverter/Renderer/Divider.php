<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ColorConverter;

/**
 * Render divider item to PageBuilder format
 */
class Divider implements RendererInterface
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
     * @var ColorConverter
     */
    private $colorConverter;

    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     * @param ColorConverter $colorConverter
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        ElementRendererInterface $elementRenderer,
        ColorConverter $colorConverter
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
        $this->colorConverter = $colorConverter;
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $eavData = isset($itemData['entityId']) ? $this->eavAttributeLoader->load($itemData['entityId']) : [];

        $rootElementAttributes = [
            'data-element' => 'main',
            'data-content-type' => 'divider',
            'data-appearance' => 'default',
            'class' => $eavData['css_classes'] ?? '',
        ];

        $formData = $itemData['formData'] ?? [];
        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );
        $rootElementAttributes['style'] .= empty($eavData) ? 'display: none;' : '';

        $lineStyle = $this->styleExtractor->createStyleFromArray([
            'border-color' => isset($eavData['color']) ? $this->colorConverter->convert($eavData['color']) :
                'rgb(206,206,206)',
            'border-width' => isset($eavData['hr_height']) ? rtrim($eavData['hr_height'], 'px') . 'px' : '1px',
            'width' => $this->normalizeSizeDimension($eavData['hr_width'] ?? ''),
            'display' => 'inline-block'
        ]);

        return $this->elementRenderer->render(
            'div',
            $rootElementAttributes,
            '<hr data-element="line" ' . ($lineStyle ? "style=\"$lineStyle\"" : '') . '/>'
        );
    }

    /**
     * Normalize values with units
     *
     * @param string $value
     * @return string
     */
    private function normalizeSizeDimension($value)
    {
        if (strpos($value, 'px') !== false || strpos($value, '%') !== false) {
            return $value;
        } elseif (!empty($value)) {
            return $value . 'px';
        }

        return '100%';
    }

    /**
     * Define the defaults for the advanced section
     *
     * @return array
     */
    private function getAdvancedDefaults()
    {
        return [
            'border-style' => 'none',
            'border-width' => '1px',
            'border-radius' => '0px',
            'margin-top' => '15px',
            'margin-bottom' => '15px'
        ];
    }
}
