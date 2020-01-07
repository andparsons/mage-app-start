<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\ResourceModel\Sku\Errors\Grid;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFactoryMock;

    /**
     * Test for loadData() method
     */
    public function testLoadData()
    {
        $productId = '3';
        $websiteId = '1';
        $sku = 'my sku';
        $typeId = 'giftcard';

        $cart = $this->getCartMock($productId, $websiteId, $sku);
        $priceCurrencyMock = $this->getPriceCurrencyMock();
        $entity = $this->getEntityFactoryMock();
        $stockStatusMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockStatusInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockRegistryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock->expects($this->any())
            ->method('getStockStatus')
            ->withAnyParameters()
            ->willReturn($stockStatusMock);
        $this->productFactoryMock =
            $this->createPartialMock(\Magento\Catalog\Api\Data\ProductInterfaceFactory::class, ['create']);
        $this->getProductMock($typeId);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $collection = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\ResourceModel\Sku\Errors\Grid\Collection::class,
            [
                'entityFactory' => $entity,
                'cart' => $cart,
                'productFactory' => $this->productFactoryMock,
                'priceCurrency' => $priceCurrencyMock,
                'stockRegistry' => $registryMock
            ]
        );
        $collection->loadData();

        foreach ($collection->getItems() as $item) {
            $product = $item->getProduct();
            if ($item->getCode() != 'failed_sku') {
                $this->assertEquals($typeId, $product->getTypeId());
                $this->assertEquals('10.00', $item->getPrice());
            }
        }
    }

    /**
     * Return cart mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\AdvancedCheckout\Model\Cart
     */
    private function getCartMock($productId, $storeId, $sku)
    {
        $cartMock = $this->getMockBuilder(
            \Magento\AdvancedCheckout\Model\Cart::class
        )->disableOriginalConstructor()->setMethods(
            ['getFailedItems', 'getStore']
        )->getMock();
        $cartMock->expects(
            $this->any()
        )->method(
            'getFailedItems'
        )->will(
            $this->returnValue(
                [
                    [
                        "item" => ["id" => $productId, "is_qty_disabled" => "false", "sku" => $sku, "qty" => "1"],
                        "code" => "failed_configure",
                        "orig_qty" => "7",
                    ],
                    [
                        "item" => ["sku" => 'invalid', "qty" => "1"],
                        "code" => "failed_sku",
                        "orig_qty" => "1"
                    ],
                ]
            )
        );
        $storeMock = $this->getStoreMock($storeId);
        $cartMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        return $cartMock;
    }

    /**
     * Return store mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    private function getStoreMock($websiteId)
    {
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));

        return $storeMock;
    }

    /**
     * Mock product instance
     *
     * @return void
     */
    private function getProductMock($typeId)
    {
        $productMock = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\ProductInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['load', 'setIsSalable', 'setCustomerGroupId', 'getData', 'getTierPrice', 'setData']
        );
        $productMock->expects($this->any())->method('load')->willReturnSelf();
        $productMock->expects($this->once())->method('getData')->with('tier_price')->willReturn(1);
        $productMock->expects($this->once())->method('getTierPrice')->will($this->returnValue('10.00'));
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($typeId));
        $this->productFactoryMock->expects($this->any())->method('create')->willReturn($productMock);
    }

    /**
     * Return PriceCurrencyInterface mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject| \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private function getPriceCurrencyMock()
    {
        $priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceCurrencyMock->expects($this->any())->method('format')->will($this->returnArgument(0));

        return $priceCurrencyMock;
    }

    /**
     * Return entityFactory mock instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Collection\EntityFactory
     */
    private function getEntityFactoryMock()
    {
        $entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);

        return $entityFactoryMock;
    }
}
