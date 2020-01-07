<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Test\Integration;

use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SimpleProductsTest
 */
class SimpleProductsTest extends AbstractProductTestHelper
{
    /**
     * Load fixtures for test
     */
    public static function loadFixture()
    {
        include __DIR__ . '/_files/setup_simple_products.php';
    }

    /**
     * Remove fixtures
     */
    public static function tearDownAfterClass()
    {
        include __DIR__ . '/_files/setup_simple_products_rollback.php';
    }

    /**
     * Validate simple product data
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @throws \Throwable
     */
    public function testSimpleProducts() : void
    {
        $this->runIndexer();

        $skus = ['simple1', 'simple2', 'simple3'];
        $storeViewCodes = ['default', 'fixture_second_store'];

        foreach ($skus as $sku) {
            $product = $this->productRepository->get($sku);
            $product->setTypeInstance(Bootstrap::getObjectManager()->create(Simple::class));

            foreach ($storeViewCodes as $storeViewCode) {
                $extractedProduct = $this->getExtractedProduct($sku, $storeViewCode);
                $this->validateBaseProductData($product, $extractedProduct, $storeViewCode);
                $this->validateCategoryData($product, $extractedProduct);
                $this->validatePricingData($product, $extractedProduct);
                $this->validateImageUrls($product, $extractedProduct);
                $this->validateAttributeData($product, $extractedProduct);
            }
        }
    }
}
