<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;

/**
 * Render map item to PageBuilder format
 */
class Map implements RendererInterface
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

        $rootElementAttributes = [
            'data-element' => 'main',
            'data-content-type' => 'map',
            'data-appearance' => 'default',
            'class' => $eavData['css_classes'] ?? '',
            'data-show-controls' => 'true',
            'data-locations' => '[]',
        ];

        $this->renderMapLocations($eavData, $rootElementAttributes);

        $formData = $itemData['formData'] ?? [];

        // Only migrate the height if it contained a valid unit
        if (isset($eavData['map_height'])
            && (strpos($eavData['map_height'], '%') !== false || strpos($eavData['map_height'], 'px') !== false)
        ) {
            $formData['height'] = isset($eavData['map_height']) && $eavData['map_height']
                && strpos($eavData['map_height'], '%') === false
                    ? rtrim($eavData['map_height'], 'px') . 'px' : '300px';
        }

        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );

        if (!isset($itemData['entityId'])) {
            $rootElementAttributes['style'] .= ' display: none;';
        }

        return $this->elementRenderer->render(
            'div',
            $rootElementAttributes
        );
    }

    /**
     * Extract and render Map Location data from EAV
     *
     * @param array $eavData
     * @param array $rootElementAttributes
     *
     * @return void
     */
    private function renderMapLocations(array $eavData, array &$rootElementAttributes) : void
    {
        if (isset($eavData['map'])) {
            $map = explode(',', $eavData['map']);
            $rootElementAttributes['data-locations'] = '[{&quot;position&quot;:{&quot;latitude&quot;:'
                . $map[0]
                . ',&quot;longitude&quot;:'
                . $map[1]
                . '}}]';
        }
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
