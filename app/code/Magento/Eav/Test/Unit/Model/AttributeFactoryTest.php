<?php
namespace Magento\Eav\Test\Unit\Model;

class AttributeFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\AttributeFactory
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_arguments = ['test1', 'test2'];

    /**
     * @var string
     */
    protected $_className = 'Test_Class';

    protected function setUp()
    {
        /** @var $objectManagerMock \Magento\Framework\ObjectManagerInterface */
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnCallback([$this, 'getModelInstance'])
        );

        $this->_factory = new \Magento\Eav\Model\AttributeFactory($objectManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_factory);
    }

    /**
     * @covers \Magento\Eav\Model\AttributeFactory::createAttribute
     */
    public function testCreateAttribute()
    {
        $this->assertEquals($this->_className, $this->_factory->createAttribute($this->_className, $this->_arguments));
    }

    /**
     * @param $className
     * @param $arguments
     * @return mixed
     */
    public function getModelInstance($className, $arguments)
    {
        $this->assertInternalType('array', $arguments);
        $this->assertArrayHasKey('data', $arguments);
        $this->assertEquals($this->_arguments, $arguments['data']);

        return $className;
    }
}
