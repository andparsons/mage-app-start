<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Test Magento\NegotiableQuote\Model\ProductOptionsProvider class.
 */
class ProductOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ProductOptionsProvider
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\NegotiableQuote\Model\ProductOptionsProvider::class, []);
    }

    /**
     * Test getProductType method.
     *
     * @return void
     */
    public function testGetProductType()
    {
        $productType = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;

        $this->assertEquals($productType, $this->model->getProductType());
    }

    /**
     * Test getOptions method.
     *
     * @return void
     */
    public function testGetOptions()
    {
        $expectedResult = [
            1 =>
                [
                    'label' => 'Option Title',
                    'values' => [
                        [
                            'value_index' => 1,
                            'label' => 'Value Title'
                        ]
                    ]
                ]
            ];
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductCustomOptionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $value = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->expects($this->once())->method('getOptionInstance')->willReturn($optionInstance);
        $optionInstance->expects($this->once())
            ->method('getProductOptions')
            ->with($product)
            ->willReturn([$option]);
        $option->expects($this->atLeastOnce())->method('getOptionId')->willReturn(1);
        $option->expects($this->once())->method('getTitle')->willReturn('Option Title');
        $option->expects($this->atLeastOnce())->method('getValues')->willReturn([$value]);
        $value->expects($this->once())->method('getOptionTypeId')->willReturn(1);
        $value->expects($this->once())->method('getTitle')->willReturn('Value Title');

        $this->assertEquals($expectedResult, $this->model->getOptions($product));
    }

    /**
     * Test getOptions method for field without values.
     *
     * @return void
     */
    public function testGetOptionsWithoutValues()
    {
        $expectedResult = [
            1 =>
                [
                    'label' => 'Option Title',
                    'values' => []
                ]
        ];
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductCustomOptionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects($this->once())->method('getOptionInstance')->willReturn($optionInstance);
        $optionInstance->expects($this->once())
            ->method('getProductOptions')
            ->with($product)
            ->willReturn([$option]);
        $option->expects($this->atLeastOnce())->method('getOptionId')->willReturn(1);
        $option->expects($this->once())->method('getTitle')->willReturn('Option Title');
        $option->expects($this->atLeastOnce())->method('getValues')->willReturn(null);

        $this->assertEquals($expectedResult, $this->model->getOptions($product));
    }
}
