<?php
declare(strict_types=1);

namespace Magento\CatalogInventoryDataExporter\Test\Integration;

use Magento\CatalogDataExporter\Test\Integration\AbstractProductTestHelper;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

class ProductInStockTest extends AbstractProductTestHelper
{

    /**
     * Load fixtures for test
     */
    public static function loadFixture()
    {
        include __DIR__ . '/_files/setup_is_in_stock.php';
    }

    /**
     * Remove fixtures
     */
    public static function tearDownAfterClass()
    {
        include __DIR__ . '/_files/setup_is_in_stock_rollback.php';
    }

    /**
     * Validate inStock status
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
    public function testProductInStock() : void
    {
        $sku = 'simple5';
        $storeViewCode = 'default';

        $this->runIndexer();
        $this->changeInStockStatus($sku);
        $this->runIndexer();

        $extractedProduct = $this->getExtractedProduct($sku, $storeViewCode);
        $this->validateProductInStock($extractedProduct);
    }

    /**
     * Change inStock status of product
     *
     * @param string $sku
     * @return void
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function changeInStockStatus(string $sku) : void
    {
        $product = $this->productRepository->get($sku);
        $productId = $product->getId();

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = Bootstrap::getObjectManager()->create(Item::class);
        $stockItem->load($productId, 'product_id');
        $stockItem->setIsInStock(false);
        $stockItem->save();
    }

    /**
     * Validate inStock status of product in extracted product data
     *
     * @param array $extractedProduct
     * @return void
     */
    protected function validateProductInStock(array $extractedProduct) : void
    {
        $this->assertEquals(false, $extractedProduct['feedData']['inStock']);
    }
}
