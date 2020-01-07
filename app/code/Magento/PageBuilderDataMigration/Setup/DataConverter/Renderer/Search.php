<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\EavAttributeLoaderInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\StyleExtractorInterface;
use Magento\PageBuilderDataMigration\Setup\DataConverter\ElementRendererInterface;

/**
 * Render search to PageBuilder format
 */
class Search implements RendererInterface
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
        /**
         * If there is no entityId don't render anything, Page Builder doesn't support search boxes so an empty one
         * would just be a useless block of HTML.
         */
        if (!isset($itemData['entityId'])) {
            return '';
        }

        $eavData = $this->eavAttributeLoader->load($itemData['entityId']);

        $searchStyles = '';
        if (isset($itemData['formData'])) {
            $style = $this->styleExtractor->extractStyle($itemData['formData']);
            if ($style) {
                $searchStyles = $style;
            }
        }
        $searchStyles .= ' float: none;';

        $searchClasses = $eavData['css_classes'] ?? '';
        $searchPlaceHolder = $eavData['placeholder'] ?? '';

        $searchHtml = "{{block class='Magento\Framework\View\Element\Template' " .
            "template='Magento_PageBuilder::content_type/search.phtml' " .
            "placeholder='$searchPlaceHolder' classes='$searchClasses' styles='$searchStyles'}}";

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
                $searchHtml
            )
        );
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
