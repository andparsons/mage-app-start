<?php
namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

class UpsellTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Upsell
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Catalog\Block\Product\ProductList\Upsell::class);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = ['compare_item_1'];
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTag));

        $itemsCollection = new \ReflectionProperty(\Magento\Catalog\Block\Product\ProductList\Upsell::class, '_items');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            $productTag,
            $this->block->getIdentities()
        );
    }
}
