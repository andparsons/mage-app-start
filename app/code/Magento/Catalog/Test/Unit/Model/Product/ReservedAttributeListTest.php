<?php
namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product\ReservedAttributeList;

class ReservedAttributeListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReservedAttributeList
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new ReservedAttributeList(
            \Magento\Catalog\Model\Product::class,
            ['some_value'],
            ['some_attribute']
        );
    }

    /**
     * @covers \Magento\Catalog\Model\Product\ReservedAttributeList::isReservedAttribute
     * @dataProvider dataProvider
     */
    public function testIsReservedAttribute($isUserDefined, $attributeCode, $expected)
    {
        $attribute = $this->createPartialMock(
            \Magento\Catalog\Model\Entity\Attribute::class,
            ['getIsUserDefined', 'getAttributeCode', '__sleep', '__wakeup']
        );

        $attribute->expects($this->once())->method('getIsUserDefined')->will($this->returnValue($isUserDefined));
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));

        $this->assertEquals($expected, $this->model->isReservedAttribute($attribute));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [false, 'some_code', false],
            [true, 'some_value', true],
            [true, 'name', true],
            [true, 'price', true],
            [true, 'category_id', true],
            [true, 'some_code', false],
        ];
    }
}
