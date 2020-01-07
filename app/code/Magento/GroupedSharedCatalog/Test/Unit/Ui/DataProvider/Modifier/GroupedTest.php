<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedSharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

/**
 * Test for Grouped modifier.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCalculator;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\GroupedSharedCatalog\Ui\DataProvider\Modifier\Grouped
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
            ->getMockForAbstractClass();
        $this->productType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storageFactory = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCalculator = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\PriceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\GroupedSharedCatalog\Ui\DataProvider\Modifier\Grouped::class,
            [
                'productRepository' => $this->productRepository,
                'productType' => $this->productType,
                'storageFactory' => $this->storageFactory,
                'priceCalculator' => $this->priceCalculator,
                'metadataPool' => $this->metadataPool,
                'request' => $this->request
            ]
        );
    }

    /**
     * Test modifyData method.
     *
     * @return void
     */
    public function testModifyData()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ->willReturn('configure_key');
        $data = [
            'entity_id' => 1,
            'website_id' => 1,
        ];
        $productSku = 'SKU1';
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAssociatedProducts'])
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
        $this->productType->expects($this->once())->method('factory')->with($product)->willReturn($abstractType);
        $abstractType->expects($this->once())->method('getAssociatedProducts')->with($product)->willReturn([$product]);
        $this->storageFactory->expects($this->once())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($this->storage);
        $this->storage->expects($this->once())->method('isProductAssigned')->with($productSku)->willReturn(true);
        $product->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $product->expects($this->atLeastOnce())->method('getPrice')->willReturn(20);
        $this->priceCalculator->expects($this->once())
            ->method('calculateNewPriceForProduct')
            ->with('configure_key', $productSku, 20)
            ->willReturnArgument(2);

        $this->assertSame(
            [
                'entity_id' => 1,
                'website_id' => 1,
                'price' => 20,
                'new_price' => 20,
            ],
            $this->modifier->modifyData($data)
        );
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
