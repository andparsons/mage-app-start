<?php
namespace Magento\CatalogInventory\Model\StockItemSave\OnProductUpdate\ByProductModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Model\StockItemSave\StockItemDataChecker;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;

class ByStockItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemInterfaceFactory
     */
    private $stockItemFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StockItemDataChecker
     */
    private $stockItemDataChecker;

    /**
     * @var array
     */
    private $stockItemData = [
        StockItemInterface::QTY => 555,
        StockItemInterface::MANAGE_STOCK => true,
        StockItemInterface::IS_IN_STOCK => false,
    ];

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->stockItemFactory = $objectManager->get(StockItemInterfaceFactory::class);
        $this->dataObjectHelper = $objectManager->get(DataObjectHelper::class);
        $this->stockItemDataChecker = $objectManager->get(StockItemDataChecker::class);
    }

    /**
     * Test saving of stock item by product data via product model (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSave()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $productData[ProductInterface::EXTENSION_ATTRIBUTES_KEY]['stock_item'] = $this->stockItemData;
        $this->dataObjectHelper->populateWithArray($product, $productData, ProductInterface::class);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }

    /**
     * Test saving of manually created stock item (and set by extension attributes object) on product save via
     * product model (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveManuallyCreatedStockItem()
    {
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockItemFactory->create();
        $this->dataObjectHelper->populateWithArray($stockItem, $this->stockItemData, StockItemInterface::class);

        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $product->getExtensionAttributes()->setStockItem($stockItem);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }

    /**
     * Test saving of manually updated stock item (obtained from extension attributes object) on product save via
     * product repository (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveManuallyUpdatedStockItem()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->dataObjectHelper->populateWithArray(
            $stockItem,
            $this->stockItemData,
            StockItemInterface::class
        );
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }
}
