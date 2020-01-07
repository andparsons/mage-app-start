<?php

namespace Magento\GroupedRequisitionList\Test\Unit\Model\Checker;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for ProductQtyChangeAvailability.
 */
class ProductQtyChangeAvailabilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedRequisitionList\Model\Checker\ProductQtyChangeAvailability
     */
    private $productQtyChangeAvailability;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->productQtyChangeAvailability = $objectManagerHelper->getObject(
            \Magento\GroupedRequisitionList\Model\Checker\ProductQtyChangeAvailability::class
        );
    }

    /**
     * Test for isAvailable().
     *
     * @param string $productType
     * @param bool $result
     * @return void
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable($productType, $result)
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getTypeId')->willReturn($productType);

        $this->assertEquals($result, $this->productQtyChangeAvailability->isAvailable($product));
    }

    /**
     * DataProvider for testIsAvailable().
     *
     * @return array
     */
    public function isAvailableDataProvider()
    {
        return [
            [\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE, false],
            ['', true]
        ];
    }
}
