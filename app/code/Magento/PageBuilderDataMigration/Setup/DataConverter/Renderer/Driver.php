<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\BackgroundImageConverter;

/**
 * Render driver to PageBuilder format
 */
class Driver implements RendererInterface
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
     * @var BackgroundImageConverter
     */
    private $backgroundImageConverter;

    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     * @param BackgroundImageConverter $backgroundImageConverter
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        ElementRendererInterface $elementRenderer,
        BackgroundImageConverter $backgroundImageConverter
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
        $this->backgroundImageConverter = $backgroundImageConverter;
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $eavData = isset($itemData['entityId']) ? $this->eavAttributeLoader->load($itemData['entityId']) : [];
        $formData = $itemData['formData'] ?? [];

        $buttonHtml = '';
        if ($this->hasButton($eavData)) {
            $buttonHtml = $this->elementRenderer->render(
                'button',
                [
                    'type' => 'button',
                    'class' => 'pagebuilder-banner-button pagebuilder-button-secondary',
                    'data-element' => 'button',
                    'style' => 'opacity: 1; visibility: visible;',
                ],
                $eavData['link_text'] ?? ''
            );
        }

        return $this->elementRenderer->render(
            'div',
            $this->getMainElementAttributes($itemData, $eavData, $formData),
            $this->elementRenderer->render(
                isset($eavData['link_url']) && !empty($eavData['link_url']) ? 'a' : 'div',
                $this->getLinkElementAttributes($eavData),
                $this->elementRenderer->render(
                    'div',
                    $this->getWrapperElementAttributes($itemData, $eavData, $formData),
                    $this->elementRenderer->render(
                        'div',
                        $this->getOverlayElementAttributes($itemData, $formData),
                        $this->elementRenderer->render(
                            'div',
                            [
                                'class' => 'pagebuilder-poster-content'
                            ],
                            $this->elementRenderer->render(
                                'div',
                                [
                                    'data-element' => 'content',
                                ]
                            ) . $buttonHtml
                        )
                    )
                )
            )
        );
    }

    /**
     * Does the driver have a button
     *
     * @param array $eavData
     *
     * @return bool
     */
    private function hasButton(array $eavData) : bool
    {
        return ((isset($eavData['link_url']) && !empty($eavData['link_url']))
            && (isset($eavData['link_text'])
                && !empty($eavData['link_text']))
            || (isset($eavData['link_text']) && !empty($eavData['link_text'])));
    }

    /**
     * Retrieve attributes for main element
     *
     * @param array $itemData
     * @param array $eavData
     * @param array $formData
     *
     * @return array
     */
    private function getMainElementAttributes(array $itemData, array $eavData, array $formData) : array
    {
        return [
            'data-element' => 'main',
            'data-content-type' => 'banner',
            'data-appearance' => 'poster',
            'data-show-button' => $this->hasButton($eavData) ? 'always' : 'never',
            'data-show-overlay' => 'never',
            'class' => $eavData['css_classes'] ?? '',
            'style' => $this->styleExtractor->extractStyle(
                $formData,
                $this->getDefaultStyles($itemData),
                [
                    'margin'
                ]
            ) . (!isset($itemData['entityId']) ? ' display: none;' : '')
        ];
    }

    /**
     * Retrieve link element attributes
     *
     * @param array $eavData
     *
     * @return array
     */
    private function getLinkElementAttributes(array $eavData) : array
    {
        return [
            'href' => $eavData['link_url'] ?? null,
            'target' => (isset($eavData['target_blank']) && (string) $eavData['target_blank'] === 'Yes'
                ? '_blank' : null),
            'data-link-type' => 'default',
            'data-element' => 'link',
        ];
    }

    /**
     * Return the wrapper element attributes
     *
     * @param array $itemData
     * @param array $eavData
     * @param array $formData
     *
     * @return array
     */
    private function getWrapperElementAttributes(array $itemData, array $eavData, array $formData) : array
    {
        $defaults = array_merge(
            $this->getDefaultStyles($itemData),
            [
                'background-position' =>
                    'center ' . (isset($formData['align']) && !empty($formData['align']) ? $formData['align'] : 'left')
            ]
        );

        return [
            'class' => 'pagebuilder-banner-wrapper',
            'data-background-images' => $this->backgroundImageConverter->convert(
                $eavData['image'] ?? null,
                $eavData['mobile_image'] ?? null
            ),
            'data-element' => 'wrapper',
            'style' => $this->styleExtractor->extractStyle(
                $formData,
                $defaults,
                [
                    'text-align',
                    'background-position',
                    'background-size',
                    'background-repeat',
                    'background-attachment',
                    'border-style',
                    'border-width',
                    'border-radius'
                ]
            )
        ];
    }

    /**
     * Retrieve attributes for overlay element
     *
     * @param array $itemData
     * @param array $formData
     *
     * @return array
     */
    private function getOverlayElementAttributes(array $itemData, array $formData) : array
    {
        return [
            'class' => 'pagebuilder-overlay pagebuilder-poster-overlay',
            'data-element' => 'overlay',
            'style' => $this->styleExtractor->extractStyle(
                $formData,
                $this->getDefaultStyles($itemData),
                [
                    'border-radius',
                    'min-height',
                    'padding',
                ]
            )
        ];
    }

    /**
     * Define the default styles for various aspects of the banner
     *
     * @param array $itemData
     *
     * @return array
     */
    private function getDefaultStyles(array $itemData) : array
    {
        return [
            'min-height' => '300px',
            'background-size' => (isset($itemData['entityId']) ? 'contain' : 'cover'),
            'background-repeat' => 'no-repeat',
            'background-attachment' => 'scroll',
            'border-style' => 'none',
            'border-width' => '1px',
            'border-radius' => '0px',
            'padding' => '40px',
            'margin-bottom' => '15px'
        ];
    }
}
