<?php
namespace Magento\RequisitionList\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for RequisitionListProduct.
 */
class RequisitionListProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Model\Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListProduct
     */
    private $requisitionListProduct;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->setMethods(['factory'])
            ->getMock();

        $productTypesToConfigure = [
            'configurable',
            'bundle',
            'grouped'
        ];

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->requisitionListProduct = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionListProduct::class,
            [
                'productRepository' => $this->productRepository,
                'serializer' => $this->serializer,
                'productType' => $this->productType,
                'productTypesToConfigure' => $productTypesToConfigure
            ]
        );
    }

    /**
     * Test for getProduct().
     *
     * @return void
     */
    public function testGetProduct()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isVisibleInCatalog'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('isVisibleInCatalog')->willReturn(true);
        $this->productRepository->expects($this->atLeastOnce())->method('get')->willReturn($product);

        $this->assertEquals($product, $this->requisitionListProduct->getProduct('sku'));
    }

    /**
     * Test for getProduct() when product is not visible in catalog.
     *
     * @return void
     */
    public function testGetProductWithProductNotVisibleInCatalog()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isVisibleInCatalog'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('isVisibleInCatalog')->willReturn(false);
        $this->productRepository->expects($this->atLeastOnce())->method('get')->willReturn($product);

        $this->assertEquals(false, $this->requisitionListProduct->getProduct('sku'));
    }

    /**
     * Test for getProduct() with NoSuchEntityException.
     *
     * @return void
     */
    public function testGetProductWithNoSuchEntityException()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('Exception'));
        $this->productRepository->expects($this->atLeastOnce())->method('get')->willThrowException($exception);

        $this->assertEquals(false, $this->requisitionListProduct->getProduct('sku'));
    }

    /**
     * Test for isProductShouldBeConfigured().
     *
     * @return void
     */
    public function testGetIsProductShouldBeConfigured()
    {
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstance->expects($this->atLeastOnce())->method('hasRequiredOptions')->willReturn(true);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getTypeId',
                'getTypeInstance'
            ])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->productType->expects($this->atLeastOnce())->method('factory')->willReturn($typeInstance);

        $this->assertTrue($this->requisitionListProduct->isProductShouldBeConfigured($product));
    }

    /**
     * Test for isProductShouldBeConfigured() for configurable product.
     *
     * @return void
     */
    public function testGetIsProductShouldBeConfiguredForConfigurableProduct()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeId')->willReturn('configurable');

        $this->assertTrue($this->requisitionListProduct->isProductShouldBeConfigured($product));
    }

    /**
     * Test for prepareProductData().
     *
     * @return void
     */
    public function testPrepareProductData()
    {
        $productData = '{"product_data":"options"}';
        $this->serializer->expects($this->atLeastOnce())->method('unserialize')
            ->willReturn(['options' => 'option_1']);

        $this->assertInstanceOf(
            \Magento\Framework\DataObject::class,
            $this->requisitionListProduct->prepareProductData($productData)
        );
    }
}
