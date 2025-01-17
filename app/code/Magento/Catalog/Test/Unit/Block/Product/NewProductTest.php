<?php
namespace Magento\Catalog\Test\Unit\Block\Product;

class NewProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Catalog\Block\Product\NewProduct::class);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $this->assertEquals([\Magento\Catalog\Model\Product::CACHE_TAG], $this->block->getIdentities());
    }

    public function testScope()
    {
        $this->assertFalse($this->block->isScopePrivate());
    }
}
