<?php

namespace Magento\Catalog\Test\Unit\Model\Product\Pricing\Renderer;

class SalableResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver
     */
    protected $object;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    protected function setUp()
    {
        $this->product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['__wakeup', 'getCanShowPrice']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver::class
        );
    }

    public function testSalableItem()
    {
        $this->product->expects($this->any())
            ->method('getCanShowPrice')
            ->willReturn(true);

        $result = $this->object->isSalable($this->product);
        $this->assertTrue($result);
    }

    public function testNotSalableItem()
    {
        $this->product->expects($this->any())
            ->method('getCanShowPrice')
            ->willReturn(false);

        $result = $this->object->isSalable($this->product);
        $this->assertFalse($result);
    }
}
