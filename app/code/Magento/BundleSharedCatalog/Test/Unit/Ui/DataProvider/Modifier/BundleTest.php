<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleSharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

/**
 * Test for Bundle modifier.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorage;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCalculator;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Model\Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\BundleSharedCatalog\Ui\DataProvider\Modifier\Bundle
     */
    private $modifier;

    /**
     * Set up for test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storageFactory = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->wizardStorage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->priceCalculator = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\PriceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\BundleSharedCatalog\Ui\DataProvider\Modifier\Bundle::class,
            [
                'productRepository' => $this->productRepository,
                'storageFactory' => $this->storageFactory,
                'priceCalculator' => $this->priceCalculator,
                'metadataPool' => $this->metadataPool,
                'productType' => $this->productType,
                'request' => $this->request
            ]
        );
    }

    /**
     * Test modifyData method.
     *
     * @param array $result
     * @param int $priceType
     * @param int $linkPriceType
     * @param int $priceTypeInvocationCounter
     * @return void
     * @dataProvider modifyDataDataProvider
     */
    public function testModifyData(array $result, $priceType, $linkPriceType, $priceTypeInvocationCounter)
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ->willReturn('configure_key');
        $data = [
            'entity_id' => 1,
            'website_id' => 1,
        ];
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttribute = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->setMethods(['getBundleProductOptions'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $bundleProductOptions = $this->getMockBuilder(\Magento\Bundle\Api\Data\OptionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $linkProduct = $this->getMockBuilder(\Magento\Bundle\Api\Data\LinkInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $entity = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entity);
        $entity->expects($this->once())->method('getIdentifierField')->willReturn('entity_id');
        $this->productRepository->expects($this->once())->method('getById')->with(1)->willReturn($product);
        $product->expects($this->atLeastOnce())
            ->method('getCustomAttribute')
            ->withConsecutive(['price_type'], ['price_type'], ['price_view'])
            ->willReturn($attribute);
        $attribute->expects($this->atLeastOnce())->method('getValue')->willReturn($priceType);
        $product->expects($this->atLeastOnce())->method('getPrice')->willReturn(120);
        $product->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttribute);
        $extensionAttribute->expects($this->atLeastOnce())
            ->method('getBundleProductOptions')
            ->willReturn([$bundleProductOptions]);
        $bundleProductOptions->expects($this->once())->method('getProductLinks')->willReturn([$linkProduct]);
        $linkProduct->expects($this->once())->method('getSku')->willReturn('test_sku');
        $this->productRepository->expects($this->once())->method('get')->willReturn($product);
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn('SKU1');
        $this->storageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($this->wizardStorage);
        $this->wizardStorage->expects($this->once())->method('isProductAssigned')->with('SKU1')->willReturn(true);
        $linkProduct->expects($this->exactly($priceTypeInvocationCounter))
            ->method('getPriceType')
            ->willReturn($linkPriceType);
        $linkProduct->expects($this->exactly($priceTypeInvocationCounter))->method('getPrice')->willReturn(20);
        $linkProduct->expects($this->once())->method('getQty')->willReturn(1);
        $bundleProductOptions->expects($this->once())->method('getRequired')->willReturn(true);
        $this->priceCalculator->expects($this->atLeastOnce())
            ->method('calculateNewPriceForProduct')
            ->willReturnArgument(2);

        $productType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSalable'])
            ->getMockForAbstractClass();
        $productType->expects($this->once())->method('isSalable')->willReturn(true);
        $this->productType->expects($this->once())->method('factory')->willReturn($productType);

        $this->assertSame($result, $this->modifier->modifyData($data));
    }

    /**
     * Data provider for modifyData method.
     *
     * @return array
     */
    public function modifyDataDataProvider()
    {
        return [
            [
                [
                    'entity_id' => 1,
                    'website_id' => 1,
                    'max_new_price' => 144.0,
                    'new_price' => 144.0,
                    'max_price' => 144.0,
                    'price' => 144.0,
                    'currency_type' => 'percent',
                    'price_view' => 1,
                    'price_type' => 1
                ],
                1,
                1,
                1
            ],
            [
                [
                    'entity_id' => 1,
                    'website_id' => 1,
                    'max_new_price' => 140,
                    'new_price' => 140,
                    'max_price' => 140,
                    'price' => 140,
                    'currency_type' => 'percent',
                    'price_view' => 1,
                    'price_type' => 1
                ],
                1,
                0,
                1
            ],
            [
                [
                    'entity_id' => 1,
                    'website_id' => 1,
                    'max_new_price' => 120,
                    'new_price' => 120,
                    'max_price' => 120,
                    'price' => 120,
                    'currency_type' => 'percent',
                    'price_view' => 0,
                    'price_type' => 0
                ],
                0,
                0,
                0
            ],
        ];
    }

    /**
     * Test modifyMeta method.
     *
     * @return void
     */
    public function testModifyMeta()
    {
        $data = ['modifyMeta'];
        $this->assertEquals($data, $this->modifier->modifyMeta($data));
    }
}
