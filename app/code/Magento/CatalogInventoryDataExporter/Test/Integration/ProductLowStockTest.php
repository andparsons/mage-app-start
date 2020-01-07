<?php
declare(strict_types=1);

namespace Magento\CatalogInventoryDataExporter\Test\Integration;

use Magento\CatalogDataExporter\Test\Integration\AbstractProductTestHelper;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

class ProductLowStockTest extends AbstractProductTestHelper
{

    /**
     * Load fixtures for test
     */
    public static function loadFixture()
    {
        include __DIR__ . '/_files/setup_is_low_stock.php';
    }

    /**
     * Remove fixtures
     */
    public static function tearDownAfterClass()
    {
        include __DIR__ . '/_files/setup_is_low_stock_rollback.php';
    }

    /**
     * Validate lowStock status
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     * @magentoConfigFixture current_store cataloginventory/options/stock_threshold_qty 20
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @throws \Throwable
     */
    public function testProductLowStock() : void
    {
        $sku = 'simple6';
        $storeViewCode = 'default';

        $this->runIndexer();
        $this->changeLowStockStatus($sku);
        $this->runIndexer();

        $extractedProduct = $this->getExtractedProduct($sku, $storeViewCode);
        $this->validateProductLowStock($extractedProduct);
    }

    /**
     * Change lowStock status of product
     *
     * @param string $sku
     * @return void
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function changeLowStockStatus(string $sku) : void
    {
        $product = $this->productRepository->get($sku);
        $productId = $product->getId();

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = Bootstrap::getObjectManager()->create(Item::class);
        $stockItem->load($productId, 'product_id');
        $stockItem->setQty(1);
        $stockItem->save();
    }

    /**
     * Validate lowStock status of product in extracted product data
     *
     * @param array $extractedProduct
     * @return void
     */
    protected function validateProductLowStock(array $extractedProduct) : void
    {
        $this->assertEquals(true, $extractedProduct['feedData']['lowStock']);
    }
}
