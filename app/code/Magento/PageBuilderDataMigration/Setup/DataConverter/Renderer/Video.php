<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;

/**
 * Render video to PageBuilder format
 */
class Video implements RendererInterface
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
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        ElementRendererInterface $elementRenderer
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
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

        // Create the video master template
        return $this->elementRenderer->render(
            'div',
            $this->getRootElementAttributes($itemData, $eavData, $formData),
            $this->elementRenderer->render(
                'div',
                [
                    'class' => 'pagebuilder-video-inner',
                    'data-element' => 'inner',
                ],
                $this->elementRenderer->render(
                    'div',
                    $this->getWrapperElementAttributes($formData),
                    $this->elementRenderer->render(
                        'div',
                        [
                            'class' => 'pagebuilder-video-container',
                        ],
                        $this->elementRenderer->render(
                            'iframe',
                            $this->getIframeAttributes($eavData)
                        )
                    )
                )
            )
        );
    }

    /**
     * Get attributes, style, and classes for the root element
     *
     * @param array $itemData
     * @param array $eavData
     * @param array $formData
     * @return array
     */
    private function getRootElementAttributes(array $itemData, array $eavData, array $formData): array
    {
        $attributes = [
            'data-element' => 'main',
            'data-content-type' => 'video',
            'data-appearance' => 'default',
            'class' => $eavData['css_classes'] ?? '',
        ];

        $attributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults(),
            [
                'text-align',
                'display',
                'margin',
            ]
        );

        if (!isset($itemData['entityId'])) {
            $attributes['style'] .= ' display: none;';
        }

        return $attributes;
    }

    /**
     * Get attributes, style, and classes for the wrapper
     *
     * @param array $formData
     * @return array
     */
    private function getWrapperElementAttributes(array $formData): array
    {
        $attributes = [
            'data-element' => 'wrapper',
            'class' => 'pagebuilder-video-wrapper',
            'style' => '',
        ];

        // Margin is included on the root element
        $style = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults(),
            [
                'border',
                'border-style',
                'border-color',
                'border-width',
                'border-radius',
                'padding'
            ]
        );

        if ($style) {
            $attributes['style'] = $style;
        }

        return $attributes;
    }

    /**
     * Get attributes, style, and classes for the iframe
     *
     * @param array $eavData
     * @return array
     */
    private function getIframeAttributes(array $eavData) : array
    {
        $attributes = [
            'data-element' => 'video',
            'src' => isset($eavData['video_url']) ? $this->getVideoEmbedUrl($eavData['video_url']) : '',
            'frameborder' => '0',
            'allowfullscreen' => 'true'
        ];

        return $attributes;
    }

    /**
     * Take a video URL and create an embed URL
     *
     * @param string $videoUrl
     *
     * @return string
     */
    private function getVideoEmbedUrl(string $videoUrl) : string
    {
        if (strpos($videoUrl, "youtu") !== false) {
            $youtubeId = explode('?v=', $videoUrl);
            if (isset($youtubeId[1])) {
                return 'https://www.youtube.com/embed/' . $youtubeId[1];
            }
        }

        if (strpos($videoUrl, "vimeo") !== false) {
            preg_match('/vimeo.com\/([0-9]+)\??/i', $videoUrl, $vimeoId);
            if (isset($vimeoId[1])) {
                return 'https://player.vimeo.com/video/' . $vimeoId[1] . '?title=0&amp;byline=0&amp;portrait=0';
            }
        }

        return $videoUrl;
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
            'margin-bottom' => '15px'
        ];
    }
}
