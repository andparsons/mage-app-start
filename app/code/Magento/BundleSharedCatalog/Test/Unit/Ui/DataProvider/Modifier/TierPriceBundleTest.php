<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleSharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Test for TierPriceBundle modifier.
 */
class TierPriceBundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $arrayManager;

    /**
     * @var \Magento\BundleSharedCatalog\Ui\DataProvider\Modifier\TierPriceBundle
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
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->arrayManager = $this->getMockBuilder(\Magento\Framework\Stdlib\ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\BundleSharedCatalog\Ui\DataProvider\Modifier\TierPriceBundle::class,
            [
                'productRepository' => $this->productRepository,
                'request' => $this->request,
                'arrayManager' => $this->arrayManager
            ]
        );
    }

    /**
     * Test modifyMeta method.
     *
     * @return void
     */
    public function testModifyMeta()
    {
        $data = [
            'product_id' => 1,
        ];
        $tierPricePath = 'tier/price/path';
        $pricePath = 'price/path';
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSalable'])
            ->getMockForAbstractClass();
        $this->request->expects($this->once())->method('getParam')->with('product_id')->willReturn(1);
        $this->productRepository->expects($this->once())->method('getById')->with(1)->willReturn($product);
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Bundle\Model\Product\Type::TYPE_CODE);
        $this->arrayManager->expects($this->at(0))
            ->method('findPath')
            ->with(ProductAttributeInterface::CODE_TIER_PRICE, $data, null, 'children')
            ->willReturn($tierPricePath);
        $this->arrayManager->expects($this->at(1))
            ->method('findPath')
            ->with(ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE, $data, $tierPricePath)
            ->willReturn($pricePath);
        $this->arrayManager->expects($this->exactly(2))
            ->method('slicePath')
            ->withConsecutive([$pricePath, 0, -1], ['price/value_type/arguments/data/options', 0, -1])
            ->willReturn('price');
        $this->arrayManager->expects($this->once())
            ->method('get')
            ->with('price/value_type/arguments/data/options', $data)
            ->willReturn([['value' => 'percent'], ['value' => 'fixed']]);
        $this->arrayManager->expects($this->once())
            ->method('remove')
            ->with('price/value_type/arguments/data/options', $data)
            ->willReturn([]);
        $this->arrayManager->expects($this->once())
            ->method('merge')
            ->with('price', [], ['options' => [['value' => 'percent']]])
            ->willReturn(['options' => [['value' => 'percent']]]);

        $this->assertSame(
            ['options' => [['value' => 'percent']]],
            $this->modifier->modifyMeta($data)
        );
    }

    /**
     * Test modifyData method.
     *
     * @return void
     */
    public function testModifyData()
    {
        $data = ['modifyData'];
        $this->assertEquals($data, $this->modifier->modifyData($data));
    }
}
