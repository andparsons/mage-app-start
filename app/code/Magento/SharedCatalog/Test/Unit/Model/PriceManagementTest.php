<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;

/**
 * Unit test for PriceManagement model.
 */
class PriceManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\PriceManagement
     */
    private $priceManagement;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productItemManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\ProductItemManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->priceManagement = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\PriceManagement::class,
            [
                'productRepository' => $this->productRepository,
                'productItemManagement' => $this->productItemManagement,
                'storeManager' => $this->storeManager
            ]
        );
    }

    /**
     * Test saveProductTierPrices().
     *
     * @return void
     */
    public function testSaveProductTierPrices()
    {
        $productId = 346;
        $priceData = [1, 2, 3];
        $prices = [$productId => $priceData];
        $this->prepareStoreManager();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->productRepository->expects($this->once())->method('getById')->willReturn($product);
        $this->productItemManagement->expects($this->once())->method('updateTierPrices')->willReturnSelf();
        $this->assertEquals(
            $this->priceManagement,
            $this->priceManagement->saveProductTierPrices($this->sharedCatalog, $prices)
        );
    }

    /**
     * Prepare StoreManager mock.
     *
     * @return void
     */
    private function prepareStoreManager()
    {
        $storeCode = 'test_store';
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()->getMock();
        $store->expects($this->atLeastOnce())->method('getCode')->willReturn($storeCode);
        $this->storeManager->expects($this->atLeastOnce())
            ->method('getStore')->with(Store::DEFAULT_STORE_ID)->willReturn($store);
        $this->storeManager->expects($this->atLeastOnce())->method('setCurrentStore')->with($storeCode);
    }

    /**
     * Test deleteProductTierPrices().
     *
     * @return void
     */
    public function testDeleteProductTierPrices()
    {
        $sku = 'SDE323425';
        $skus = [$sku];
        $this->prepareStoreManager();
        $this->productItemManagement->expects($this->once())
            ->method('deleteTierPricesBySku')->with($this->sharedCatalog, $skus)->willReturnSelf();
        $this->assertEquals(
            $this->priceManagement,
            $this->priceManagement->deleteProductTierPrices($this->sharedCatalog, $skus)
        );
    }
}
