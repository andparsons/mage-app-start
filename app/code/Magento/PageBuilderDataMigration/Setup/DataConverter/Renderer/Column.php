<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;

/**
 * Render column to PageBuilder format
 */
class Column implements RendererInterface
{
    // We have to map as we are unable to calculate the new column width effectively for all sizes
    const COLUMN_WIDTH_MAPPING = [
        '0.167' => '16.6667',
        '0.250' => '25',
        '0.333' => '33.3333',
        '0.500' => '50',
        '0.666' => '66.6666',
        '0.750' => '75',
        '0.825' => '82.5000',
        '1.000' => '100',
    ];

    /**
     * @var StyleExtractorInterface
     */
    private $styleExtractor;

    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param ElementRendererInterface $elementRenderer
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        ElementRendererInterface $elementRenderer
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->elementRenderer = $elementRenderer;
    }

    /**
     * @inheritdoc
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $formData = $itemData['formData'] ?? [];

        if (!isset($formData['width'])) {
            throw new \InvalidArgumentException('Width is required to migrate column.');
        }

        $rootElementAttributes = [
            'data-element' => 'main',
            'data-content-type' => 'column',
            'data-appearance' => 'full-height',
            'class' => $formData['css_classes'] ?? '',
            'style' => '',
        ];

        // Generate our styles
        $width = $formData['width'];
        unset($formData['width']);
        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            array_merge(
                [
                    'width' => $this->calculateColumnWidth($width)
                ],
                $this->getAdvancedDefaults()
            )
        );

        return $this->elementRenderer->render(
            'div',
            $rootElementAttributes,
            isset($additionalData['children']) ? $additionalData['children'] : ''
        );
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
            'padding-left' => '7.5px',
            'padding-right' => '7.5px',
        ];
    }

    /**
     * Calculate the column width to 4 decimal places
     *
     * @param string $oldWidth
     *
     * @return string
     */
    private function calculateColumnWidth($oldWidth) : string
    {
        $formattedWidth = number_format(floatval($oldWidth), 3);
        if (!isset(self::COLUMN_WIDTH_MAPPING[$formattedWidth])) {
            /**
             * If we cannot directly match the mapping, attempt to locate a mapping within 0.01 to resolve rounding
             * issues.
             */
            foreach (self::COLUMN_WIDTH_MAPPING as $original => $width) {
                if (floatval($formattedWidth) >= $original - 0.01 && floatval($formattedWidth) <= $original + 0.01) {
                    return $width . '%';
                }
            }

            throw new \InvalidArgumentException('Width ' . $oldWidth .' has no valid mapping.');
        }

        return self::COLUMN_WIDTH_MAPPING[$formattedWidth] . '%';
    }
}
