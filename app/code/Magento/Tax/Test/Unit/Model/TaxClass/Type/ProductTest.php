<?php
namespace Magento\Tax\Test\Unit\Model\TaxClass\Type;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    public function testIsAssignedToObjects()
    {
        $collectionClassName = \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class;
        $collectionMock = $this->getMockBuilder($collectionClassName)
            ->setMethods(['addAttributeToFilter', 'getSize'])->disableOriginalConstructor()->getMock();
        $collectionMock->expects($this->once())->method('addAttributeToFilter')
            ->with($this->equalTo('tax_class_id'), $this->equalTo(1))->will($this->returnSelf());
        $collectionMock->expects($this->once())->method('getSize')
            ->will($this->returnValue(1));

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getCollection', '__wakeup', 'getEntityId'])
            ->disableOriginalConstructor()->getMock();
        $productMock->expects($this->once())->method('getCollection')->will($this->returnValue($collectionMock));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var $model \Magento\Tax\Model\TaxClass\Type\Product */
        $model = $objectManagerHelper->getObject(
            \Magento\Tax\Model\TaxClass\Type\Product::class,
            ['modelProduct' => $productMock, 'data' => ['id' => 1]]
        );
        $this->assertTrue($model->isAssignedToObjects());
    }
}
