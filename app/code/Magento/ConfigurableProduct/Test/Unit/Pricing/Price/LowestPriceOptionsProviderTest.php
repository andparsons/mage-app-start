<?php

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

class LowestPriceOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var LinkedProductSelectBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linkedProductSelectBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollection;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    protected function setUp()
    {
        $this->connection = $this
            ->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMock();
        $this->resourceConnection = $this
            ->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();
        $this->resourceConnection->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->linkedProductSelectBuilder = $this
            ->getMockBuilder(LinkedProductSelectBuilderInterface::class)
            ->setMethods(['build'])
            ->getMock();
        $this->productCollection = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addAttributeToSelect', 'addIdFilter', 'getItems'])
            ->getMock();
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->productCollection);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider::class,
            [
                'resourceConnection' => $this->resourceConnection,
                'linkedProductSelectBuilder' => $this->linkedProductSelectBuilder,
                'collectionFactory' => $this->collectionFactory,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    public function testGetProducts()
    {
        $productId = 1;
        $linkedProducts = ['some', 'linked', 'products', 'dataobjects'];
        $product = $this->getMockBuilder(ProductInterface::class)->disableOriginalConstructor()->getMock();
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $this->linkedProductSelectBuilder->expects($this->any())->method('build')->with($productId)->willReturn([]);
        $this->productCollection
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id'])
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addIdFilter')->willReturnSelf();
        $this->productCollection->expects($this->once())->method('getItems')->willReturn($linkedProducts);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(Store::DEFAULT_STORE_ID)
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);

        $this->assertEquals($linkedProducts, $this->model->getProducts($product));
    }
}
