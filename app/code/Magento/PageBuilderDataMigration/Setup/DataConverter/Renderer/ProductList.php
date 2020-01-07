<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Helper\Conditions;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Render product list to PageBuilder format
 */
class ProductList implements RendererInterface
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
     * @var \Magento\Widget\Helper\Conditions
     */
    private $conditionsHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Json
     */
    private $jsonEncoder;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     * @param Conditions $conditionsHelper
     * @param Escaper $escaper
     * @param Json $jsonEncoder
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        ElementRendererInterface $elementRenderer,
        Conditions $conditionsHelper,
        Escaper $escaper,
        Json $jsonEncoder
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
        $this->conditionsHelper = $conditionsHelper;
        $this->escaper = $escaper;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @inheritdoc
     *
     * @throws NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $eavData = isset($itemData['entityId']) ? $this->eavAttributeLoader->load($itemData['entityId']) : [];

        $categoryId = $eavData['category_id'] ?? '';
        $conditions = [
            '1' => [
                'type' => \Magento\CatalogWidget\Model\Rule\Condition\Combine::class,
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => '',
            ],
            '1--1' => [
                'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                'attribute' => 'category_ids',
                'operator' => '==',
                'value' => $categoryId,
            ]
        ];

        $productsCount = $eavData['product_count'] ?? 5;
        $conditionsEncoded = $this->conditionsHelper->encode($conditions);
        $widgetString = "{{widget type=\"Magento\CatalogWidget\Block\Product\ProductsList\" " .
            "template=\"Magento_CatalogWidget::product/widget/content/grid.phtml\" " .
            "type_name=\"Catalog Products List\" anchor_text=\"\" id_path=\"\" show_pager=\"0\" " .
            "products_count=\"$productsCount\" conditions_encoded=\"$conditionsEncoded\"}}";

        $rootElementAttributes = [
            'data-element' => 'main',
            'data-content-type' => 'products',
            'data-appearance' => 'grid',
            'class' => $eavData['css_classes'] ?? '',
        ];

        $formData = $itemData['formData'] ?? [];
        $rootElementAttributes['style'] = $this->styleExtractor->extractStyle(
            $formData,
            $this->getAdvancedDefaults()
        );

        if (!isset($itemData['entityId'])) {
            $rootElementAttributes['style'] .= ' display: none;';
        }

        return $this->elementRenderer->render(
            'div',
            $rootElementAttributes,
            $widgetString
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
            'border-radius' => '0px'
        ];
    }
}
