<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\PageBuilderDataMigration\Setup\DataConverter\NoSuchEntityException;
use Magento\Widget\Helper\Conditions;

/**
 * Render product to PageBuilder format
 */
class Product implements RendererInterface
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    private $conditionsHelper;

    /**
     * @param StyleExtractorInterface $styleExtractor
     * @param EavAttributeLoaderInterface $eavAttributeLoader
     * @param ElementRendererInterface $elementRenderer
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param Conditions $conditionsHelper
     */
    public function __construct(
        StyleExtractorInterface $styleExtractor,
        EavAttributeLoaderInterface $eavAttributeLoader,
        ElementRendererInterface $elementRenderer,
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        Conditions $conditionsHelper
    ) {
        $this->styleExtractor = $styleExtractor;
        $this->eavAttributeLoader = $eavAttributeLoader;
        $this->elementRenderer = $elementRenderer;
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->conditionsHelper = $conditionsHelper;
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $eavData = isset($itemData['entityId']) ? $this->eavAttributeLoader->load($itemData['entityId']) : [];

        if (isset($eavData['product_id'])) {
            $connection = $this->resourceConnection->getConnection();
            $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('catalog_product_entity'), ['sku'])
                ->where($productMetadata->getIdentifierField() . ' = ?', (int) $eavData['product_id']);
            $productSku = $connection->fetchOne($select);
            if (!$productSku) {
                throw new NoSuchEntityException(__('Product with id %1 does not exist.', $eavData['product_id']));
            }

            $conditions = [
                '1' => [
                    'type' => \Magento\CatalogWidget\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => [
                    'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                    'attribute' => 'sku',
                    'operator' => '==',
                    'value' => $productSku,
                ]
            ];

            $conditionsEncoded = $this->conditionsHelper->encode($conditions);
            $widgetString = "{{widget type=\"Magento\CatalogWidget\Block\Product\ProductsList\" " .
                "template=\"Magento_CatalogWidget::product/widget/content/grid.phtml\" " .
                "type_name=\"Catalog Products List\" anchor_text=\"\" id_path=\"\" show_pager=\"0\" " .
                "products_count=\"1\" conditions_encoded=\"$conditionsEncoded\"}}";
        } else {
            $widgetString = '';
        }

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
