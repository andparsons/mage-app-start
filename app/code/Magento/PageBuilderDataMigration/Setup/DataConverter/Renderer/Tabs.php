<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;

/**
 * Render tabs to PageBuilder format
 */
class Tabs implements RendererInterface
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
    private $tabItemEavAttributeLoader;

    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @var TabsItem
     */
    private $tabsItemRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param EavAttributeLoaderInterface $tabItemEavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     * @param TabsItem $tabsItemRenderer
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        EavAttributeLoaderInterface $tabItemEavAttributeLoader,
        ElementRendererInterface $elementRenderer,
        TabsItem $tabsItemRenderer
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->tabItemEavAttributeLoader = $tabItemEavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
        $this->tabsItemRenderer = $tabsItemRenderer;
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $eavData = isset($itemData['entityId']) ? $this->eavAttributeLoader->load($itemData['entityId']) : [];

        // If there is no entity ID ensure we render an empty tab item as well
        if (!isset($additionalData['children'])) {
            $additionalData['children'] = $this->tabsItemRenderer->render(
                [],
                [
                    'parentChildIndex' => 1,
                    'childIndex' => 1,
                ]
            );
        }

        $formData = $itemData['formData'] ?? [];

        $tabItems = [];
        if (isset($itemData['children']) && isset($itemData['children']['tabs_items'])) {
            $tabItems = $itemData['children']['tabs_items'];
        }

        return $this->elementRenderer->render(
            'div',
            $this->getMainElementAttributes($itemData, $eavData, $formData),
            $this->elementRenderer->render(
                'ul',
                [
                    'data-element' => 'navigation',
                    'role' => 'tablist',
                    'class' => 'tabs-navigation',
                    'style' => 'text-align: left;'
                ],
                $this->renderTabHeaders(
                    $additionalData['childIndex'],
                    $tabItems,
                    $formData
                )
            )
            . $this->elementRenderer->render(
                'div',
                [
                    'data-element' => 'content',
                    'class' => 'tabs-content',
                    'style' => $this->getContentStyles($formData)
                ],
                (isset($additionalData['children']) ? $additionalData['children'] : '')
            )
        );
    }

    /**
     * Retrieve the main element attributes
     *
     * @param array $itemData
     * @param array $eavData
     * @param array $formData
     *
     * @return array
     */
    private function getMainElementAttributes(array $itemData, array $eavData, array $formData) : array
    {
        $rootElementAttributes = [
            'data-element' => 'main',
            'data-content-type' => 'tabs',
            'data-appearance' => 'default',
            'class' => $eavData['css_classes'] ?? ''
        ];
        $rootElementAttributes['class'] .= ' tab-align-left';

        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults(),
            [
                'margin',
                'padding',
            ]
        );

        if (!isset($itemData['entityId'])) {
            $rootElementAttributes['style'] .= ' display: none;';
        }

        return $rootElementAttributes;
    }

    /**
     * Retrieve the content styles
     *
     * @param array $formData
     *
     * @return string
     */
    private function getContentStyles(array $formData) : string
    {
        $contentStyles = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults(),
            [
                'border-style',
                'border-color',
                'border-width',
                'border-radius',
                'text-align',
            ]
        );
        $contentStyles .= ' min-height: 300px;';
        return $contentStyles;
    }

    /**
     * Render the tab headers
     *
     * @param int $childIndex
     * @param array $tabItems
     * @param array $formData
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function renderTabHeaders(int $childIndex, array $tabItems, array $formData): string
    {
        $headersStyle = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults(),
            [
                'border-style',
                'border-radius',
                'border-color',
                'border-width'
            ]
        );

        // If we have no tab items we need to at least generate a default tab heading
        if (count($tabItems) === 0 || count($tabItems) > 0 && !isset($tabItems[0]['entityId'])) {
            return '<li data-element="headers" role="tab" class="tab-header"'
                . ' style="' . $headersStyle . '">'
                . '<a href="#tab' . $childIndex . '-0" class="tab-title" title="Tab 1">'
                . '<span class="tab-title">Tab 1</span>'
                . '</a>'
                . '</li>';
        }

        $tabHeaderElementHtml = '';
        foreach ($tabItems as $tabIndex => $tabItem) {
            $tabItemEavData = $this->tabItemEavAttributeLoader->load($tabItem['entityId']);
            $tabId = 'tab' . $childIndex . '-' . $tabIndex;
            $tabHeaderElementHtml .= '<li data-element="headers" role="tab" class="tab-header"'
                . ' style="' . $headersStyle . '">'
                . '<a href="#' . $tabId . '" class="tab-title" title="' . $tabItemEavData['title'] . '">'
                . '<span class="tab-title">' . $tabItemEavData['title'] . '</span>'
                . '</a>'
                . '</li>';
        }
        return $tabHeaderElementHtml;
    }

    /**
     * Define the defaults for the advanced section
     *
     * @return array
     */
    private function getAdvancedDefaults()
    {
        return [
            'border-width' => '1px',
            'border-radius' => '0px',
            'margin-bottom' => '15px'
        ];
    }
}
