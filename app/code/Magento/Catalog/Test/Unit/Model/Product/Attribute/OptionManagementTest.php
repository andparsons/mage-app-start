<?php
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

class OptionManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\OptionManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavOptionManagementMock;

    protected function setUp()
    {
        $this->eavOptionManagementMock = $this->createMock(\Magento\Eav\Api\AttributeOptionManagementInterface::class);
        $this->model = new \Magento\Catalog\Model\Product\Attribute\OptionManagement(
            $this->eavOptionManagementMock
        );
    }

    public function testGetItems()
    {
        $attributeCode = 10;
        $this->eavOptionManagementMock->expects($this->once())
            ->method('getItems')
            ->with(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn([]);
        $this->assertEquals([], $this->model->getItems($attributeCode));
    }

    public function testAdd()
    {
        $attributeCode = 42;
        $optionMock = $this->createMock(\Magento\Eav\Api\Data\AttributeOptionInterface::class);
        $this->eavOptionManagementMock->expects($this->once())->method('add')->with(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionMock
        )->willReturn(true);
        $this->assertTrue($this->model->add($attributeCode, $optionMock));
    }

    public function testDelete()
    {
        $attributeCode = 'atrCde';
        $optionId = 'opt';
        $this->eavOptionManagementMock->expects($this->once())->method('delete')->with(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionId
        )->willReturn(true);
        $this->assertTrue($this->model->delete($attributeCode, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid option id
     */
    public function testDeleteWithInvalidOption()
    {
        $attributeCode = 'atrCde';
        $optionId = '';
        $this->eavOptionManagementMock->expects($this->never())->method('delete');
        $this->model->delete($attributeCode, $optionId);
    }
}
