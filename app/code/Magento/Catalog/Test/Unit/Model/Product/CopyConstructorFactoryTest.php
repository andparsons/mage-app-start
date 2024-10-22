<?php
namespace Magento\Catalog\Test\Unit\Model\Product;

use \Magento\Catalog\Model\Product\CopyConstructorFactory;

class CopyConstructorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CopyConstructorFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new CopyConstructorFactory($this->_objectManagerMock);
    }

    public function testCreateWithInvalidType()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage(
            'Magento\Framework\DataObject does not implement \Magento\Catalog\Model\Product\CopyConstructorInterface'
        );
        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_model->create(\Magento\Framework\DataObject::class);
    }

    public function testCreateWithValidType()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Catalog\Model\Product\CopyConstructor\Composite::class
        )->will(
            $this->returnValue('object')
        );
        $this->assertEquals(
            'object',
            $this->_model->create(\Magento\Catalog\Model\Product\CopyConstructor\Composite::class)
        );
    }
}
