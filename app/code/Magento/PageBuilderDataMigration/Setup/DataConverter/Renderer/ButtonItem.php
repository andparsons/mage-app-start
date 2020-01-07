<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;

/**
 * Render button item to PageBuilder format
 */
class ButtonItem implements RendererInterface
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
            'data-content-type' => 'button-item',
            'data-appearance' => 'default',
            'style' => 'display: inline-block;',
            'class' => $eavData['css_classes'] ?? ''
        ];

        $formData = $itemData['formData'] ?? [];
        $buttonStyleAttribute = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );

        $rootElementHtml = '<div';
        foreach ($rootElementAttributes as $attributeName => $attributeValue) {
            $rootElementHtml .= $attributeValue ? " $attributeName=\"$attributeValue\"" : '';
        }

        $linkNodeName = isset($eavData['link_url']) ? 'a' : 'div';
        $linkDataElementName = isset($eavData['link_url']) ? 'link' : 'empty_link';

        $rootElementHtml .= '><' . $linkNodeName . ' data-element="' . $linkDataElementName . '"'
            . ' data-link-type="default"'
            . (isset($eavData['link_url']) ? ' href="' . $eavData['link_url'] . '"' : '')
            . ' style="' . $buttonStyleAttribute . '"'
            . ' class="pagebuilder-button-secondary"><span data-element="link_text">'
            . ($eavData['link_text'] ?? '')
            . '</span></' . $linkNodeName . '></div>';

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
            'text-align' => 'center',
        ];
    }
}
