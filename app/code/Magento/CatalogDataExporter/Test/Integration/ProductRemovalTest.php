<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Test\Integration;

use Magento\CatalogDataExporter\Model\Feed\Products;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

class ProductRemovalTest extends AbstractProductTestHelper
{
    /**
     * Load fixtures for test
     */
    public static function loadFixture()
    {
        include __DIR__ . '/_files/setup_product_removal.php';
    }

    /**
     * Remove fixtures
     */
    public static function tearDownAfterClass()
    {
        include __DIR__ . '/_files/setup_product_removal_rollback.php';
    }

    /**
     * @var mixed
     */
    private $productFeed;

    /**
     * Integration test setup
     */
    protected function setUp()
    {
        parent::setUp();
        $this->productFeed = Bootstrap::getObjectManager()->create(Products::class);
    }

    /**
     * Validate product removal
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Throwable
     */
    public function testProductRemoval() : void
    {
        $sku = 'simple4';
        $this->runIndexer();
        $this->deleteProduct($sku);
        $this->runIndexer();
        $output = $this->productFeed->getFeedSince('1');
        foreach ($output['feed'] as $extractedProduct) {
            $this->validateProductRemoval($extractedProduct);
        }
    }

    /**
     * Delete product from catalog_data_exporter_products
     *
     * @param string $sku
     * @return void
     */
    protected function deleteProduct(string $sku) : void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        try {
            $product = $this->productRepository->get($sku);
            if ($product->getId()) {
                $this->productRepository->delete($product);
            }
        } catch (\Exception $e) {
            //Nothing to delete
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Validate product removal
     *
     * @param array $extractedProduct
     * @return void
     */
    protected function validateProductRemoval(array $extractedProduct) : void
    {
        $this->assertEquals(true, $extractedProduct['deleted']);
        $this->assertArrayHasKey('modifiedAt', $extractedProduct);
    }
}
