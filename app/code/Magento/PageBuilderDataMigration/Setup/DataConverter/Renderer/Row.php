<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\BackgroundImageConverter;

/**
 * Render row to PageBuilder format
 */
class Row implements RendererInterface
{
    /**
     * @var StyleExtractorInterface
     */
    private $styleExtractor;

    /**
     * @var BackgroundImageConverter
     */
    private $backgroundImageConverter;

    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param BackgroundImageConverter $backgroundImageConverter
     * @param ElementRendererInterface $elementRenderer
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        BackgroundImageConverter $backgroundImageConverter,
        ElementRendererInterface $elementRenderer
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->backgroundImageConverter = $backgroundImageConverter;
        $this->elementRenderer = $elementRenderer;
    }

    /**
     * @inheritdoc
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $formData = $itemData['formData'] ?? [];

        // Extract the styles applied from the advanced section, provide advanced defaults
        $style = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );

        $childrenHtml = (isset($additionalData['children']) ? $additionalData['children'] : '');

        // Return an altered appearance for full width
        if (isset($formData['template']) && $formData['template'] === 'full-width.phtml') {
            return $this->renderFullWidth($formData, $style, $childrenHtml);
        }

        // All other rows default to our new default of contained
        return $this->renderContained($formData, $style, $childrenHtml);
    }

    /**
     * Render full width appearance
     *
     * @param array $formData
     * @param string $style
     * @param string $childrenHtml
     *
     * @return string
     */
    private function renderFullWidth($formData, $style, $childrenHtml) : string
    {
        return $this->elementRenderer->render(
            'div',
            [
                'data-element' => 'main',
                'data-content-type' => 'row',
                'data-appearance' => 'full-width',
                'data-background-images' => $this->backgroundImageConverter->convert(
                    $formData['background_image'] ?? null
                ),
                'class' => $formData['css_classes'] ?? '',
                'style' => $style ?? null
            ],
            $this->elementRenderer->render(
                'div',
                [
                    'data-element' => 'inner',
                    'class' => 'row-full-width-inner',
                ],
                $childrenHtml
            )
        );
    }

    /**
     * Render contained appearance
     *
     * @param array $formData
     * @param string $style
     * @param string $childrenHtml
     *
     * @return string
     */
    private function renderContained($formData, $style, $childrenHtml) : string
    {
        return $this->elementRenderer->render(
            'div',
            [
                'data-element' => 'main',
                'data-content-type' => 'row',
                'data-appearance' => 'contained',
            ],
            $this->elementRenderer->render(
                'div',
                [
                    'data-element' => 'inner',
                    'data-background-images' => $this->backgroundImageConverter->convert(
                        $formData['background_image'] ?? null
                    ),
                    'class' => $formData['css_classes'] ?? '',
                    'style' => $style ?? null
                ],
                $childrenHtml
            )
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
            'display' => 'flex',
            'flex-direction' => 'column',
        ];
    }
}
