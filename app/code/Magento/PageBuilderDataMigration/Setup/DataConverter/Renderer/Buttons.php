<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;

/**
 * Render buttons to PageBuilder format
 */
class Buttons implements RendererInterface
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
     * @var ButtonItem
     */
    private $buttonItemRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     * @param ButtonItem $buttonItemRenderer
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        ElementRendererInterface $elementRenderer,
        ButtonItem $buttonItemRenderer
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
        $this->buttonItemRenderer = $buttonItemRenderer;
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $eavData = isset($itemData['entityId']) ? $this->eavAttributeLoader->load($itemData['entityId']) : [];

        // Determine if the child output is equal to an empty child output
        $isHidden = (isset($itemData['entityId']) && isset($additionalData['children'])
            && $additionalData['children'] === $this->buttonItemRenderer->render([]));

        // If there is no entity ID ensure we render an empty button item as well
        if (!isset($itemData['entityId'])) {
            $additionalData['children'] = $this->buttonItemRenderer->render([]);
            $isHidden = true;
        }

        $rootElementAttributes = [
            'data-element' => 'main',
            'data-content-type' => 'buttons',
            'data-appearance' => 'inline',
            'class' => $eavData['css_classes'] ?? ''
        ];

        $formData = $itemData['formData'] ?? [];
        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );
        $rootElementAttributes['style'] .= $isHidden ? ' display: none;' : '';

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
            'margin-bottom' => '15px'
        ];
    }
}
