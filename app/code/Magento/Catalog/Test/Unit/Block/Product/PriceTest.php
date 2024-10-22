<?php
namespace Magento\Catalog\Test\Unit\Block\Product;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Price
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Catalog\Block\Product\Price::class);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTags));
        $this->block->setProduct($product);
        $this->assertEquals($productTags, $this->block->getIdentities());
    }
}
