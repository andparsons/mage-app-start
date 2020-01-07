<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;

/**
 * Render advanced slider to PageBuilder format
 */
class AdvancedSlider implements RendererInterface
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
     * @var AdvancedSliderItem
     */
    private $advancedSliderItemRenderer;

    /**
     * @var ElementRendererInterface
     */
    private $elementRenderer;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param AdvancedSliderItem $advancedSliderItemRenderer
     * @param ElementRendererInterface $elementRenderer
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        AdvancedSliderItem $advancedSliderItemRenderer,
        ElementRendererInterface $elementRenderer
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->advancedSliderItemRenderer = $advancedSliderItemRenderer;
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

        $cssClasses = ($eavData['css_classes'] ?? '') . ' pagebuilder-slider';

        $formData = $itemData['formData'] ?? [];
        $rootElementAttributes = $this->getRootElementAttributes($eavData, $cssClasses);
        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );

        // Determine if the child output is equal to an empty child output
        $isHidden = (isset($itemData['entityId']) && isset($additionalData['children'])
            && $additionalData['children'] === $this->advancedSliderItemRenderer->render([]));

        // If there is no entity ID ensure we render an empty button item as well
        if (!isset($additionalData['children'])) {
            $additionalData['children'] = $this->advancedSliderItemRenderer->render([]);
            // By default in Page Builder show dots is enabled if the slider is new
            $eavData['show_dots'] = 'Yes';
            $isHidden = true;
        }

        if ($isHidden) {
            $rootElementAttributes['style'] .= ' display: none;';
        }

        return $this->elementRenderer->render(
            'div',
            $rootElementAttributes,
            $additionalData['children']
        );
    }

    /**
     * Retrieve the root element attributes
     *
     * @param array $eavData
     * @param string $cssClasses
     *
     * @return array
     */
    private function getRootElementAttributes(array $eavData, string $cssClasses) : array
    {
        return [
            'data-element' => 'main',
            'data-content-type' => 'slider',
            'data-appearance' => 'default',
            'data-autoplay' => $this->isEavKeyEqualTo($eavData, 'autoplay', 'Yes')  ? 'true' : 'false',
            'data-autoplay-speed' => isset($eavData['autoplay_speed']) ? $eavData['autoplay_speed'] : '4000',
            'data-fade' => $this->isEavKeyEqualTo($eavData, 'fade', 'Yes') ? 'true' : 'false',
            'data-infinite-loop' => $this->isEavKeyEqualTo($eavData, 'is_infinite', 'Yes') ? 'true' : 'false',
            'data-show-arrows' => $this->isEavKeyEqualTo($eavData, 'show_arrows', 'Yes') ? 'true' : 'false',
            'data-show-dots' => $this->isEavKeyEqualTo($eavData, 'show_dots', 'No') ? 'false' : 'true',
            'class' => $cssClasses,
        ];
    }

    /**
     * Determine if a value in the EAV data is equal to a value
     *
     * @param array $eavData
     * @param string $key
     * @param string $expectedValue
     *
     * @return bool
     */
    private function isEavKeyEqualTo(array $eavData, string $key, string $expectedValue) : bool
    {
        return isset($eavData[$key]) && (string) $eavData[$key] === $expectedValue;
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
            'min-height' => '300px',
            'margin-bottom' => '15px'
        ];
    }
}
