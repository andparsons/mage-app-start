<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\BackgroundImageConverter;

/**
 * Render advanced slide and slide item to PageBuilder format
 */
class AdvancedSliderItem implements RendererInterface
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
                    'class' => 'pagebuilder-slide-button pagebuilder-button-secondary',
                    'data-element' => 'button',
                    'style' => 'opacity: 1; visibility: visible;',
                ],
                $eavData['link_text']
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
                        $this->getOverlayElementAttributes($eavData),
                        $this->elementRenderer->render(
                            'div',
                            [
                                'class' => 'pagebuilder-collage-content'
                            ],
                            $this->elementRenderer->render(
                                'div',
                                [
                                    'data-element' => 'content',
                                ],
                                $this->getContentHtml($eavData)
                            )
                            . $buttonHtml
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
     * Return the content HTML
     *
     * @param array $eavData
     *
     * @return string
     */
    private function getContentHtml(array $eavData) : string
    {
        $showOverlay = (isset($eavData['has_overlay'])) && (string) $eavData['has_overlay'] === 'Yes';

        $contentHtml = '';
        if (isset($eavData['title'])) {
            $contentHtml .= '<h3'. ($showOverlay ? ' style="color: white;"' : '') .'>' . $eavData['title'] . '</h3>';
        }
        if (isset($eavData['textarea'])) {
            // If we have an overlay ensure all P tags are white
            if ($showOverlay) {
                $dom = new \DOMDocument();
                $dom->loadHTML($eavData['textarea'], LIBXML_HTML_NODEFDTD);
                $xpath = new \DOMXPath($dom);
                foreach ($xpath->query("//p") as $paragraph) {
                    $paragraph->setAttribute('style', 'color: white;');
                }
                // Remove the doctype and other bloat
                $contentHtml .= substr(
                    $dom->saveHTML(),
                    strpos($dom->saveHTML(), '<body>') + 6,
                    (strrpos($dom->saveHTML(), '</body>')) - strlen($dom->saveHTML())
                );
            } else {
                $contentHtml .= $eavData['textarea'];
            }
        }

        return $contentHtml;
    }

    /**
     * Retrieve attributes for the main element
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
            'data-content-type' => 'slide',
            'data-appearance' => $this->getAppearance($formData),
            'data-show-button' => $this->hasButton($eavData) ? 'always' : 'never',
            'data-show-overlay' =>
                (isset($eavData['has_overlay'])) && (string) $eavData['has_overlay'] === 'Yes' ? 'always' : 'never',
            'data-slide-name' => $eavData['title'] ?? $eavData['title_tag'] ?? 'Slide',
            'class' => $eavData['css_classes'] ?? '',
            'style' => $this->styleExtractor->extractStyle(
                $formData,
                $this->getDefaultStyles($itemData),
                [
                    'margin'
                ]
            )
        ];
    }

    /**
     * Retrieve appearance for content type
     *
     * @param array $formData
     *
     * @return string
     */
    private function getAppearance(array $formData) : string
    {
        $appearances = [
            'left' => 'collage-left',
            'center' => 'collage-centered',
            'right' => 'collage-right'
        ];
        return isset($formData['align']) && !empty($formData['align']) && isset($appearances[$formData['align']])
            ? $appearances[$formData['align']] : $appearances['left'];
    }

    /**
     * Retrieve attributes for the link element
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
     * Retrieve attributes for wrapper element
     *
     * @param array $itemData
     * @param array $eavData
     * @param array $formData
     *
     * @return array
     */
    private function getWrapperElementAttributes(array $itemData, array $eavData, array $formData) : array
    {
        $modifiedFormData = $formData;
        $modifiedFormData['align'] = 'center';
        return [
            'class' => 'pagebuilder-slide-wrapper',
            'data-background-images' => $this->backgroundImageConverter->convert(
                $eavData['image'] ?? $eavData['background_image'] ?? null,
                $eavData['mobile_image'] ?? null
            ),
            'data-element' => 'wrapper',
            'style' => $this->styleExtractor->extractStyle(
                $modifiedFormData,
                $this->getDefaultStyles($itemData),
                [
                    'min-height',
                    'text-align',
                    'background-position',
                    'background-size',
                    'background-repeat',
                    'background-attachment',
                    'border-style',
                    'border-width',
                    'border-radius',
                    'padding'
                ]
            )
        ];
    }

    /**
     * Retrieve attributes for overlay element
     *
     * @param array $eavData
     *
     * @return array
     */
    private function getOverlayElementAttributes(array $eavData) : array
    {
        $showOverlay = (isset($eavData['has_overlay'])) && (string) $eavData['has_overlay'] === 'Yes';

        return [
            'class' => 'pagebuilder-overlay',
            'data-element' => 'overlay',
            'data-overlay-color' => ($showOverlay ? 'rgba(0,0,0,0.9)' : null),
            'style' => ($showOverlay ? ' background-color: rgba(0,0,0,0.9);' : '')
        ];
    }

    /**
     * Define the default styles for various aspects of the banner
     *
     * @param array $itemData
     *
     * @return array
     */
    private function getDefaultStyles(array $itemData)
    {
        return [
            'text-align' => 'center',
            'min-height' => '300px',
            'background-position' => (isset($itemData['entityId']) ? 'center center' : 'top left'),
            'background-size' => (isset($itemData['entityId']) ? 'cover' : 'contain'),
            'background-repeat' => 'no-repeat',
            'background-attachment' => 'scroll',
            'border-style' => 'none',
            'border-width' => '1px',
            'border-radius' => '0px',
            'padding' => '40px',
        ];
    }
}
