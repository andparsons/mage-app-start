<?php
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin\AttributeSet;

class IndexableAttributeFilterTest extends \PHPUnit\Framework\TestCase
{
    public function testFilter()
    {
        $catalogResourceMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'isIndexable', '__wakeup'])
            ->getMock();
        $catalogResourceMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());
        $catalogResourceMock->expects($this->at(1))
            ->method('isIndexable')
            ->will($this->returnValue(true));
        $catalogResourceMock->expects($this->at(2))
            ->method('isIndexable')
            ->will($this->returnValue(false));

        $eavAttributeFactoryMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $eavAttributeFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($catalogResourceMock));

        $attributeMock1 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getAttributeId', 'getAttributeCode', 'load', '__wakeup'])
            ->getMock();
        $attributeMock1->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('indexable_attribute'));
        $attributeMock1->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $attributeMock2 = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getAttributeId', 'getAttributeCode', 'load', '__wakeup'])
            ->getMock();
        $attributeMock2->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('non_indexable_attribute'));
        $attributeMock2->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $attributes = [$attributeMock1, $attributeMock2];

        $groupMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributes', '__wakeup'])
            ->getMock();
        $groupMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $attributeSetMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Set::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGroups', '__wakeup'])
            ->getMock();
        $attributeSetMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$groupMock]));

        $model = new \Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter(
            $eavAttributeFactoryMock
        );

        $this->assertEquals(['indexable_attribute'], $model->filter($attributeSetMock));
    }
}
