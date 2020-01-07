<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftCardSharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

/**
 * Test for GiftCard modifier.
 */
class GiftCardTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\GiftCardSharedCatalog\Ui\DataProvider\Modifier\GiftCard
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
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\GiftCardSharedCatalog\Ui\DataProvider\Modifier\GiftCard::class,
            [
                'productRepository' => $this->productRepository,
                'productType' => $this->productType,
                'metadataPool' => $this->metadataPool,
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
        $data = [
            'entity_id' => 1,
            'type_id' => 'fixed'
        ];
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $price = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMinAmount'])
            ->getMock();
        $entity = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entity);
        $entity->expects($this->once())->method('getIdentifierField')->willReturn('entity_id');
        $this->productRepository->expects($this->once())->method('getById')->with(1)->willReturn($product);
        $this->productType->expects($this->once())->method('priceFactory')->with('fixed')->willReturn($price);
        $price->expects($this->once())->method('getMinAmount')->with($product)->willReturn(15);

        $this->assertSame(
            [
                'entity_id' => 1,
                'type_id' => 'fixed',
                'price' => 15,
                'new_price' => 15
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
