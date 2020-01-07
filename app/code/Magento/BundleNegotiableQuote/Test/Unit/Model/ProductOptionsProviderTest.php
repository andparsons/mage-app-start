<?php

namespace Magento\BundleNegotiableQuote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for ProductOptionsProvider.
 */
class ProductOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\BundleNegotiableQuote\Model\ProductOptionsProvider
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
            \Magento\BundleNegotiableQuote\Model\ProductOptionsProvider::class
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
            \Magento\Bundle\Model\Product\Type::TYPE_CODE,
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
        $select = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSelectionId', 'getName'])
            ->getMock();
        $select->expects($this->atLeastOnce())->method('getSelectionId')->willReturn(2);
        $select->expects($this->atLeastOnce())->method('getName')->willReturn('product name');
        $selection = $this->getMockBuilder(\Magento\Bundle\Api\Data\OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionId', 'getTitle', 'getSelections'])
            ->getMockForAbstractClass();
        $selection->expects($this->atLeastOnce())->method('getOptionId')->willReturn(1);
        $selection->expects($this->atLeastOnce())->method('getTitle')->willReturn('title');
        $selection->expects($this->atLeastOnce())->method('getSelections')->willReturn([$select]);
        $optionsCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionsCollection->expects($this->atLeastOnce())->method('appendSelections')->willReturn([$selection]);
        $selectionsCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreFilter', 'getOptionsCollection', 'getSelectionsCollection', 'getOptionsIds'])
            ->getMockForAbstractClass();
        $typeInstance->expects($this->atLeastOnce())->method('setStoreFilter')->willReturnSelf();
        $typeInstance->expects($this->atLeastOnce())->method('getOptionsCollection')->willReturn($optionsCollection);
        $typeInstance->expects($this->atLeastOnce())->method('getSelectionsCollection')
            ->willReturn($selectionsCollection);
        $typeInstance->expects($this->atLeastOnce())->method('getOptionsIds')->willReturn([1, 2, 3]);
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance', 'getStoreId'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeInstance')->willReturn($typeInstance);
        $optionsArray = [
            1 => [
                'label' => 'title',
                'values' => [
                    0 => [
                        'value_index' => 2,
                        'label' => 'product name'
                    ]
                ]
            ]
        ];

        $this->assertEquals($optionsArray, $this->productOptionsProvider->getOptions($product));
    }
}
