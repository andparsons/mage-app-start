<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Render accordions to PageBuilder format
 */
class Accordion implements RendererInterface
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
     * @var EavAttributeLoaderInterface
     */
    private $itemEavAttributeLoader;

    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @var AccordionItem
     */
    private $accordionItemRenderer;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param EavAttributeLoaderInterface $itemEavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     * @param AccordionItem $accordionItemRenderer
     * @param Json $serializer
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        EavAttributeLoaderInterface $itemEavAttributeLoader,
        ElementRendererInterface $elementRenderer,
        AccordionItem $accordionItemRenderer,
        Json $serializer
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->itemEavAttributeLoader = $itemEavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
        $this->accordionItemRenderer = $accordionItemRenderer;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        /**
         * If there is no entityId don't render anything, Page Builder doesn't support accordions so an empty one
         * would just be a useless block of HTML.
         */
        if (!isset($itemData['entityId'])) {
            return '';
        }

        $eavData = $this->eavAttributeLoader->load($itemData['entityId']);

        // If there is no entity ID ensure we render an empty button item as well
        if (!isset($additionalData['children'])) {
            $additionalData['children'] = $this->accordionItemRenderer->render([]);
        }

        $rootElementAttributes = [
            'data-mage-init' => $this->getMageInitValue($itemData),
            'class' => 'pagebuilder-accordion ' . ($eavData['css_classes'] ?? '')
        ];

        $rootElementAttributes['class'] = rtrim($rootElementAttributes['class']);

        $formData = $itemData['formData'] ?? [];
        $style = $this->styleExtractor->extractStyle($formData);
        if ($style) {
            $rootElementAttributes['style'] = $style;
        }

        $accordionHtml = '<div';
        foreach ($rootElementAttributes as $attributeName => $attributeValue) {
            $accordionHtml .= $attributeValue ? " $attributeName=\"$attributeValue\"" : '';
        }
        $accordionHtml .= '>' . $additionalData['children'] . '</div>';

        return $this->elementRenderer->render(
            'div',
            [
                'data-element' => 'main',
                'data-content-type' => 'html',
                'data-appearance' => 'default',
                'style' => $this->styleExtractor->extractStyle(
                    [],
                    $this->getHtmlAdvancedDefaults()
                )
            ],
            htmlentities(
                $accordionHtml
            )
        );
    }

    /**
     * Get data-mage-init attribute value
     *
     * @param array $itemData
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMageInitValue(array $itemData) : string
    {
        $children = isset($itemData['children']['accordion_items']) ? $itemData['children']['accordion_items'] : null;
        return htmlentities(
            $this->serializer->serialize(
                [
                    'accordion' => [
                        'active' => !empty($children) ? $this->getActiveItem($children) : [0],
                        'collapsibleElement' => '[data-collapsible=true]',
                        'content' => '[data-content=true]'
                    ]
                ]
            )
        );
    }

    /**
     * Determine which accordion items are active
     *
     * @param array $children
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getActiveItem(array $children) : array
    {
        if (!isset($child['entityId'])) {
            return [0];
        }

        $active = [];
        foreach ($children as $index => $child) {
            $eavData = $this->itemEavAttributeLoader->load($child['entityId']);
            if (isset($eavData['open_on_load']) && $eavData['open_on_load']) {
                $active[] = $index;
            }
        }

        if (empty($active)) {
            $active = [0];
        }

        return $active;
    }

    /**
     * Define the defaults for the advanced section
     *
     * @return array
     */
    private function getHtmlAdvancedDefaults()
    {
        return [
            'border-style' => 'none',
            'border-width' => '1px',
            'border-radius' => '0px',
            'margin-bottom' => '15px'
        ];
    }
}
