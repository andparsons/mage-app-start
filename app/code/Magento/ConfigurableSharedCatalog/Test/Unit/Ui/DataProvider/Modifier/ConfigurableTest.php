<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableSharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

/**
 * Test for Configurable modifier.
 */
class ConfigurableTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\ConfigurableSharedCatalog\Ui\DataProvider\Modifier\Configurable
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
            ->setMethods(['calculateNewPriceForProduct'])
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\ConfigurableSharedCatalog\Ui\DataProvider\Modifier\Configurable::class,
            [
                'productRepository' => $this->productRepository,
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
     * @param array $result
     * @param array $childs
     * @return void
     * @dataProvider modifyDataDataProvider
     */
    public function testModifyData(array $result, array $childs)
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->with(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
            ->willReturn('configure_key');
        $data = [
            'entity_id' => 1,
            'website_id' => 1,
        ];
        $linkProductSku = 'SKU1';
        $configurableProductLinks = [2, 3];
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $extensionAttribute = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->setMethods(['getConfigurableProductLinks'])
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
        $product->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttribute);
        $extensionAttribute->expects($this->atLeastOnce())
            ->method('getConfigurableProductLinks')
            ->willReturn($configurableProductLinks);
        $this->productRepository->expects($this->at(0))->method('getById')->with(1)->willReturn($product);
        foreach ($childs as $key => $child) {
            $linkProduct = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->setMethods(['getId', 'getPrice'])
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $this->wizardStorage->expects($this->at($key))
                ->method('isProductAssigned')
                ->with($linkProductSku)
                ->willReturn(true);
            $this->priceCalculator->expects($this->at($key))
                ->method('calculateNewPriceForProduct')
                ->willReturn($child['new_price']);
            $linkProduct->expects($this->atLeastOnce())->method('getSku')->willReturn($linkProductSku);
            $linkProduct->expects($this->atLeastOnce())->method('getPrice')->willReturn($child['price']);
            $this->productRepository->expects($this->at($key + 1))
                ->method('getById')->with($child['entity_id'])
                ->willReturn($linkProduct);
        }
        $this->storageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(['key' => 'configure_key'])
            ->willReturn($this->wizardStorage);

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
                    'price' => 120,
                    'new_price' => 125,
                ],
                [
                    [
                        'entity_id' => 2,
                        'price' => 120,
                        'new_price' => 125,
                    ],
                    [
                        'entity_id' => 3,
                        'price' => 140,
                        'new_price' => 145,
                    ]
                ],
            ],
            [
                [
                    'entity_id' => 1,
                    'website_id' => 1,
                    'price' => 100,
                    'new_price' => 10,
                ],
                [
                    [
                        'entity_id' => 2,
                        'price' => 200,
                        'new_price' => 10,
                    ],
                    [
                        'entity_id' => 3,
                        'price' => 100,
                        'new_price' => 20,
                    ]
                ],
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
