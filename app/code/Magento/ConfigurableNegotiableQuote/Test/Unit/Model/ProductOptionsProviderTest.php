<?php

namespace Magento\ConfigurableNegotiableQuote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for ProductOptionsProvider.
 */
class ProductOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableNegotiableQuote\Model\ProductOptionsProvider
     */
    private $productOptionsProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->productOptionsProvider = $objectManagerHelper->getObject(
            \Magento\ConfigurableNegotiableQuote\Model\ProductOptionsProvider::class
        );
    }

    /**
     * Test for getProductType().
     *
     * @return void
     */
    public function testGetProductType()
    {
        $this->assertEquals(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
            $this->productOptionsProvider->getProductType()
        );
    }

    /**
     * Test for getOptions().
     *
     * @return void
     */
    public function testGetOptions()
    {
        $configurableAttributes = ['configurable_attributes'];
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableAttributesAsArray'])
            ->getMockForAbstractClass();
        $typeInstance->expects($this->atLeastOnce())->method('getConfigurableAttributesAsArray')
            ->willReturn($configurableAttributes);
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeInstance')->willReturn($typeInstance);

        $this->assertEquals($configurableAttributes, $this->productOptionsProvider->getOptions($product));
    }
}
