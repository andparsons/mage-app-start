<?php
declare(strict_types=1);

namespace Magento\CatalogInventoryDataExporter\Test\Integration;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogDataExporter\Test\Integration\AbstractProductTestHelper;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Product buyable flag test
 *
 * @magentoDataFixture loadFixture
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ProductBuyableTest extends AbstractProductTestHelper
{
    /**
     * Test constants
     */
    const SKU = 'simple7';
    const STORE_VIEW_CODE = 'default';

    /**
     * Load fixtures for test
     */
    public static function loadFixture()
    {
        include __DIR__ . '/_files/setup_buyable_product.php';
    }

    /**
     * Remove fixtures
     */
    public static function tearDownAfterClass()
    {
        include __DIR__ . '/_files/setup_buyable_product_rollback.php';
    }

    /**
     * Validate buyable status of out of stock product
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @throws \Throwable
     */
    public function testOutOfStockProduct() : void
    {
        $this->runIndexer();
        $this->setIsInStock(false);
        $this->runIndexer();
        $this->validateProductBuyable($this->getExtractedProduct(self::SKU, self::STORE_VIEW_CODE));
    }

    /**
     * Validate buyable status of disabled product
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testDisabledProduct() : void
    {
        $this->runIndexer();
        $this->disableProduct();
        $this->runIndexer();
        $this->validateDisabledProduct($this->getExtractedProduct(self::SKU, self::STORE_VIEW_CODE));
    }

    /**
     * Validate buyable status of enabled product
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testEnabledProduct() : void
    {
        $this->runIndexer();
        $this->enableProduct();
        $this->setIsInStock(true);
        $this->runIndexer();
        $this->validateEnabledProduct($this->getExtractedProduct(self::SKU, self::STORE_VIEW_CODE));
    }

    /**
     * Set is in stock value of product
     *
     * @param bool $isInStock
     * @return void
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function setIsInStock(bool $isInStock) : void
    {
        $product = $this->productRepository->get(self::SKU);
        $productId = $product->getId();

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = Bootstrap::getObjectManager()->create(Item::class);
        $stockItem->load($productId, 'product_id');
        $stockItem->setIsInStock($isInStock);
        $stockItem->save();
    }

    /**
     * Set product status to disabled
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function disableProduct() : void
    {
        $product = $this->productRepository->get(self::SKU, true);
        $product->setStatus(Status::STATUS_DISABLED);
        $this->productRepository->save($product);
    }

    /**
     * Set product status to enabled
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function enableProduct() : void
    {
        $product = $this->productRepository->get(self::SKU, true);
        $product->setStatus(Status::STATUS_ENABLED);
        $this->productRepository->save($product);
    }

    /**
     * Validate buyable status of product in extracted product data
     *
     * @param array $extractedProduct
     * @return void
     */
    protected function validateProductBuyable(array $extractedProduct) : void
    {
        $this->assertEquals(false, $extractedProduct['feedData']['buyable']);
    }

    /**
     * Validate buyable status of disabled product in extracted product data
     *
     * @param array $extractedProduct
     * @return void
     */
    protected function validateDisabledProduct(array $extractedProduct) : void
    {
        $this->assertEquals(false, $extractedProduct['feedData']['buyable']);
    }

    /**
     * Validate buyable status of enabled product in extracted product data
     *
     * @param array $extractedProduct
     * @return void
     */
    protected function validateEnabledProduct(array $extractedProduct) : void
    {
        $this->assertEquals(true, $extractedProduct['feedData']['buyable']);
    }
}
