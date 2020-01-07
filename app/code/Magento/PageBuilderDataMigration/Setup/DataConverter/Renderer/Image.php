<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;

/**
 * Render image to PageBuilder format
 */
class Image implements RendererInterface
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

        $rootElementAttributes = [
            'data-element' => 'main',
            'data-appearance' => 'full-width',
            'data-content-type' => 'image',
            'class' => $eavData['css_classes'] ?? ''
        ];

        $formData = $itemData['formData'] ?? [];
        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            [
                'margin-bottom' => '15px'
            ]
        );

        if (!isset($itemData['entityId'])) {
            $rootElementAttributes['style'] .= ' display: none;';
        }

        $imageDefaultStyles = 'border-style: none; border-width: 1px; border-radius: 0px; max-width: 100%; ' .
            'height: auto;';

        $imageAttributes = [
            'data-element' => 'desktop_image',
            'src' => isset($eavData['image']) ? '{{media url=wysiwyg' . $eavData['image'] . '}}' : '',
            'alt' => $eavData['alt_tag'] ?? '',
            'title' => $eavData['title_tag'] ?? '',
            'style' => $imageDefaultStyles
        ];

        $mobileImageHtml = '';
        if (isset($eavData['mobile_image'])) {
            $mobileImageAttributes = [
                'data-element' => 'mobile_image',
                'src' => '{{media url=wysiwyg' . $eavData['mobile_image'] . '}}',
                'alt' => $eavData['alt_tag'] ?? '',
                'title' => $eavData['title_tag'] ?? '',
                'style' => $imageDefaultStyles
            ];
            $imageAttributes['class'] = 'pagebuilder-mobile-hidden';

            $mobileImageHtml = '<img'
                . $this->printAttributes($mobileImageAttributes)
                . ' class="pagebuilder-mobile-only">';
        }

        $captionHtml = '';
        if (isset($eavData['show_caption']) && $eavData['show_caption']->getText() == "Yes"
            && isset($eavData['title_tag'])
        ) {
            $captionHtml .= '<figcaption data-element="caption">' . $eavData['title_tag'] . '</figcaption>';
        }

        return '<figure' . $this->printAttributes($rootElementAttributes) . '>'
            . '<img' . $this->printAttributes($imageAttributes) . '>'
            . $mobileImageHtml
            . $captionHtml
            . '</figure>';
    }

    /**
     * Print HTML attributes
     *
     * @param array $elementAttributes
     * @return string
     */
    private function printAttributes($elementAttributes): string
    {
        $elementAttributesHtml = '';
        foreach ($elementAttributes as $attributeName => $attributeValue) {
            $elementAttributesHtml .= $attributeValue !== '' ? " $attributeName=\"$attributeValue\"" : '';
        }
        return $elementAttributesHtml;
    }
}
