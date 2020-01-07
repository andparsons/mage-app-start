<?php

namespace Magento\RequisitionList\Test\Unit\Model\Checker;

/**
 * Unit test for ProductChangesAvailability model.
 */
class ProductChangesAvailabilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Checker\ProductQtyChangeAvailabilityInterface
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $checker;

    /**
     * @var \Magento\RequisitionList\Model\Checker\ProductChangesAvailability
     */
    private $productChangesAvailability;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->checker = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\Checker\ProductQtyChangeAvailabilityInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productChangesAvailability = $objectManager->getObject(
            \Magento\RequisitionList\Model\Checker\ProductChangesAvailability::class,
            [
                'productQtyChangeAvailabilityCheckers' => [$this->checker],
                'ignoreTypes' => [\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            ]
        );
    }

    /**
     * Test for isProductEditable method.
     *
     * @param string $productType
     * @param int $optionsCalls
     * @param bool $expectedResult
     * @return void
     * @dataProvider isProductEditableDataProvider
     */
    public function testIsProductEditable($productType, $optionsCalls, $expectedResult)
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getTypeId', 'getTypeInstance'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $product->expects($this->once())->method('getTypeId')->willReturn($productType);
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()->getMock();
        $typeInstance->expects($this->exactly($optionsCalls))->method('hasOptions')->willReturn(false);
        $product->expects($this->exactly($optionsCalls))->method('getTypeInstance')->willReturn($typeInstance);
        $this->assertEquals($expectedResult, $this->productChangesAvailability->isProductEditable($product));
    }

    /**
     * Test for isQtyChangeAvailable method.
     *
     * @param bool $expectedResult
     * @return void
     * @dataProvider isQtyChangeAvailableDataProvider
     */
    public function testIsQtyChangeAvailable($expectedResult)
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->checker->expects($this->once())->method('isAvailable')->with($product)->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->productChangesAvailability->isQtyChangeAvailable($product));
    }

    /**
     * Data provider for testIsProductEditable.
     *
     * @return array
     */
    public function isProductEditableDataProvider()
    {
        return [
            [\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, 1, false],
            [\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE, 0, true],
        ];
    }

    /**
     * Data provider for testIsQtyChangeAvailable.
     *
     * @return array
     */
    public function isQtyChangeAvailableDataProvider()
    {
        return [[true], [false]];
    }
}
