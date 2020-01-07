<?php
declare(strict_types=1);

namespace Magento\ConfigurableProductDataExporter\Test\Integration;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogDataExporter\Test\Integration\AbstractProductTestHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ConfigurableProductsTest
 *
 * @magentoDataFixture loadFixture
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ConfigurableProductsTest extends AbstractProductTestHelper
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * Load fixtures for test
     */
    public static function loadFixture()
    {
        include __DIR__ . '/_files/setup_configurable_products.php';
    }

    /**
     * Remove fixtures
     */
    public static function tearDownAfterClass()
    {
        include __DIR__ . '/_files/setup_configurable_products_rollback.php';
    }

    /**
     * Setup tests
     */
    protected function setUp()
    {
        parent::setUp();
        $this->configurable = Bootstrap::getObjectManager()->create(Configurable::class);
    }

    /**
     * Validate configurable product data
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @throws \Throwable
     */
    public function testConfigurableProducts() : void
    {
        $this->runIndexer();

        $skus = ['configurable1'];
        $storeViewCodes = ['default', 'fixture_second_store'];
        $attributeCodes = ['test_configurable'];

        foreach ($skus as $sku) {
            $product = $this->productRepository->get($sku);
            $product->setTypeInstance(Bootstrap::getObjectManager()->create(Configurable::class));

            foreach ($storeViewCodes as $storeViewCode) {
                $extractedProduct = $this->getExtractedProduct($sku, $storeViewCode);
                $this->validateBaseProductData($product, $extractedProduct, $storeViewCode);
                $this->validateCategoryData($product, $extractedProduct);
                $this->validatePricingData($product, $extractedProduct);
                $this->validateImageUrls($product, $extractedProduct);
                $this->validateAttributeData($product, $extractedProduct);
                $this->validateOptionsData($product, $extractedProduct);
                $this->validateVariantsData($product, $extractedProduct, $attributeCodes);
            }
        }
    }

    /**
     * Validate parent product data
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @throws \Throwable
     */
    public function testParentProducts() : void
    {
        $this->runIndexer();

        $skus = ['simple_option_50', 'simple_option_60', 'simple_option_70'];
        $storeViewCodes = ['default', 'fixture_second_store'];

        foreach ($skus as $sku) {
            $product = $this->productRepository->get($sku);
            $product->setTypeInstance(Bootstrap::getObjectManager()->create(Configurable::class));

            foreach ($storeViewCodes as $storeViewCode) {
                $extractedProduct = $this->getExtractedProduct($sku, $storeViewCode);
                $this->validateParentData($product, $extractedProduct);
            }
        }
    }

    /**
     * Validate product's parent data
     *
     * @param ProductInterface $product
     * @param array $extractedProduct
     * @return void
     * @throws NoSuchEntityException
     */
    private function validateParentData(ProductInterface $product, array $extractedProduct) : void
    {
        $parents = [];
        $parentIds = $this->configurable->getParentIdsByChild($product->getId());
        foreach ($parentIds as $parentId) {
            $parentProduct = $this->productRepository->getById($parentId);
            $parents[] = [
                'sku' => $parentProduct->getSku(),
                'productType' => $parentProduct->getTypeId()
            ];
        }
        $this->assertEquals($parents, $extractedProduct['feedData']['parents']);
    }

    /**
     * Validate product options in extracted product data
     *
     * @param ProductInterface $product
     * @param array $extractedProduct
     * @return void
     */
    private function validateOptionsData(ProductInterface $product, array $extractedProduct) : void
    {
        $productOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();
        $options = [];
        foreach ($productOptions as $productOption) {
            $options[] = [
                'type' => $product->getTypeId(),
                'required' => (bool) $productOption['product_attribute']['is_required'],
                'multi' => false,
                'title' => $productOption->getLabel(),
                'values' => $this->getOptionValues($productOption->getOptions())
            ];
        }
        $extractedOptions = $this->removeGeneratedIdsFromExtractedOptions($extractedProduct);
        $this->assertEquals($options, $extractedOptions);
    }

    /**
     * Validate product variants in extracted product data
     *
     * @param ProductInterface $product
     * @param array $extract
     * @param array $attributeCodes
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    private function validateVariantsData(ProductInterface $product, array $extract, array $attributeCodes) : void
    {
        $childIds = $product->getExtensionAttributes()->getConfigurableProductLinks();
        $variants = [];
        foreach ($childIds as $childId) {
            $childProduct = $this->productRepository->getById($childId);
            $childProductPricing = $this->getPricingData($childProduct);
            $variants[] = [
                'sku' => $childProduct->getSku(),
                'minimumPrice' => [
                    'regularPrice' => $childProductPricing['price'],
                    'finalPrice' => $childProductPricing['final_price']
                ],
                'selections' => $this->getVariantSelections($childProduct, $attributeCodes)
            ];
        }
        $this->assertEquals($variants, $extract['feedData']['variants']);
    }

    /**
     * Get option values to compare to extracted product data
     *
     * @param array $optionValues
     * @return array
     */
    private function getOptionValues(array $optionValues) : array
    {
        $values = [];
        foreach ($optionValues as $optionValue) {
            $values[] = [
                'value' => $optionValue['store_label'],
                'price' => null
            ];
        }
        return $values;
    }

    /**
     * Remove generated IDs from the extracted product for data comparison
     *
     * @param array $extractedProduct
     * @return array
     */
    private function removeGeneratedIdsFromExtractedOptions(array $extractedProduct) : array
    {
        $extractedOptions = $extractedProduct['feedData']['options'];
        foreach ($extractedOptions as &$extractedOption) {
            unset($extractedOption['id']);
            foreach ($extractedOption['values'] as &$extractedOptionValue) {
                unset($extractedOptionValue['id']);
            }
        }
        return $extractedOptions;
    }

    /**
     * Get variant selections data
     *
     * @param ProductInterface $childProduct
     * @param array $attributeCodes
     * @return array
     */
    private function getVariantSelections(ProductInterface $childProduct, array $attributeCodes) : array
    {
        $selections = [];
        foreach ($attributeCodes as $attributeCode) {
            $selections[] = [
                'name' => $childProduct->getAttributes()[$attributeCode]['store_label'],
                'value' => $childProduct->getAttributeText($attributeCode)
            ];
        }
        return $selections;
    }
}
