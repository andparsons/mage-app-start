<?php

namespace Magento\Solr\Test\Unit\Model\Adapter\Container;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Solr\Model\Adapter\Container\Attribute;

/**
 * Unit test for Magento\Solr\Model\Adapter\Container\Attribute
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Solr\Model\Adapter\Container\Attribute
     */
    private $attribute;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attribute = new Attribute(
            $this->collectionMock
        );
    }

    /**
     * @return void
     */
    public function testGetAttributeCodeById()
    {
        $attributeId = 555;
        $attributeCode = 'test_attr_code1';
        $expected = 'test_attr_code1';
        $this->mockAttributeById($attributeId, $attributeCode);
        $result = $this->attribute->getAttributeCodeById($attributeId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetOptionsAttributeCodeById()
    {
        $attributeId = 'options';
        $expected = 'options';
        $result = $this->attribute->getAttributeCodeById($attributeId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetAttributeIdByCode()
    {
        $attributeId = 100;
        $attributeCode = 'test_attribute_code';
        $this->mockAttributeByCode($attributeId, $attributeCode);
        $result = $this->attribute->getAttributeIdByCode($attributeCode);
        $this->assertEquals($attributeId, $result);
    }

    public function testGetOptionsAttributeIdByCode()
    {
        $attributeCode = 'options';
        $expected = 'options';
        $result = $this->attribute->getAttributeIdByCode($attributeCode);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetMultipleAttributeIdsByCode()
    {
        $firstAttributeId = 100;
        $firstAttributeCode = 'test_attribute_code_100';
        $this->mockAttributeByCode($firstAttributeId, $firstAttributeCode, 0);
        $this->assertEquals($firstAttributeId, $this->attribute->getAttributeIdByCode($firstAttributeCode));

        $secondAttributeId = 200;
        $secondAttributeCode = 'test_attribute_code_200';
        $this->mockAttributeByCode($secondAttributeId, $secondAttributeCode, 0);
        $this->assertEquals($secondAttributeId, $this->attribute->getAttributeIdByCode($secondAttributeCode));
    }

    /**
     * @return void
     */
    public function testGetAttributeByIdTwice()
    {
        $attributeId = 555;
        $attributeCode = 'test_attr_code2';
        $expected = 'test_attr_code2';
        $this->mockAttributeById($attributeId, $attributeCode, 0);
        $this->assertEquals($expected, $this->attribute->getAttributeCodeById($attributeId));
        $this->assertEquals($expected, $this->attribute->getAttributeCodeById($attributeId));
    }

    /**
     * @return void
     */
    public function testGetAttributeByIdCachedInGetAttributeByCode()
    {
        $attributeId = 100;
        $attributeCode = 'test_attribute_code';
        $this->mockAttributeByCode($attributeId, $attributeCode);
        $this->assertEquals($attributeId, $this->attribute->getAttributeIdByCode($attributeCode));
        $this->assertEquals($attributeCode, $this->attribute->getAttributeCodeById($attributeId));
    }

    /**
     * @return void
     */
    public function testGetSearchableAttribute()
    {
        $attributeCode = 'searchable_attr_code_120';
        $attribute = $this->createAttributeMock(120, $attributeCode);
        $searchableAttributes = [
            $attribute
        ];
        $this->mockSearchableAttributes($searchableAttributes);
        $this->assertEquals($attribute, $this->attribute->getSearchableAttribute($attributeCode));
    }

    /**
     * @return void
     */
    public function testGetUnknownSearchableAttribute()
    {
        $attributeCode = 'searchable_attr_code_120';
        $searchableAttributes = [
            $this->createAttributeMock(120, 'searchable_attribute_code')
        ];
        $this->mockSearchableAttributes($searchableAttributes);
        $this->assertEquals(null, $this->attribute->getSearchableAttribute($attributeCode));
    }

    /**
     * @return void
     */
    public function testGetSearchableAttributes()
    {
        $searchableAttributes = [
            'searchable_attr_1_mock' => $this->createAttributeMock(1, 'searchable_attr_1_mock'),
            'searchable_attr_20_mock' => $this->createAttributeMock(20, 'searchable_attr_20_mock'),
            'searchable_attr_25_mock' => $this->createAttributeMock(25, 'searchable_attr_25_mock'),
            'searchable_attr_40_mock' => $this->createAttributeMock(40, 'searchable_attr_40_mock'),
            'searchable_attr_73_mock' => $this->createAttributeMock(73, 'searchable_attr_73_mock'),
            'searchable_attr_52_mock' => $this->createAttributeMock(52, 'searchable_attr_52_mock'),
            'searchable_attr_97_mock' => $this->createAttributeMock(97, 'searchable_attr_97_mock'),
        ];
        $this->mockSearchableAttributes($searchableAttributes);
        $this->assertEquals($searchableAttributes, $this->attribute->getSearchableAttributes());
    }

    /**
     * @param array $attributes
     * @return void
     */
    private function mockSearchableAttributes(array $attributes)
    {
        $this->collectionMock->expects($this->once())
            ->method('addToIndexFilter')
            ->with(true)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));
    }

    /**
     * @param int $attributeId
     * @param string $attributeCode
     * @param int $sequence
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockAttributeById($attributeId, $attributeCode, $sequence = 0)
    {
        $attribute = $this->createAttributeMock($attributeId, $attributeCode);
        $this->collectionMock->expects($this->at($sequence))
            ->method('getItemById')
            ->with($attributeId)
            ->willReturn($attribute);
        return $attribute;
    }

    /**
     * @param int $attributeId
     * @param string $attributeCode
     * @param int $sequence
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockAttributeByCode($attributeId, $attributeCode, $sequence = 0)
    {
        $attribute = $this->createAttributeMock($attributeId, $attributeCode);
        $this->collectionMock->expects($this->at($sequence))
            ->method('getItemByColumnValue')
            ->with('attribute_code', $attributeCode)
            ->willReturn($attribute);
        return $attribute;
    }

    /**
     * @param $attributeId
     * @param $attributeCode
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($attributeId, $attributeCode)
    {
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->setMethods(['getAttributeCode', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attribute->method('getId')
            ->willReturn($attributeId);
        return $attribute;
    }
}
