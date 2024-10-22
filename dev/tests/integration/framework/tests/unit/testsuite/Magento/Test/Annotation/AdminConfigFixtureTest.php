<?php

/**
 * Test class for \Magento\TestFramework\Annotation\AdminConfigFixture.
 */
namespace Magento\Test\Annotation;

class AdminConfigFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\AdminConfigFixture|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->createPartialMock(
            \Magento\TestFramework\Annotation\AdminConfigFixture::class,
            ['_getConfigValue', '_setConfigValue']
        );
    }

    /**
     * @magentoAdminConfigFixture any_config some_value
     */
    public function testConfig()
    {
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'any_config'
        )->will(
            $this->returnValue('some_value')
        );
        $this->_object->expects($this->at(1))->method('_setConfigValue')->with('any_config', 'some_value');
        $this->_object->startTest($this);

        $this->_object->expects($this->once())->method('_setConfigValue')->with('any_config', 'some_value');
        $this->_object->endTest($this);
    }

    public function testInitStoreAfterOfScope()
    {
        $this->_object->expects($this->never())->method('_getConfigValue');
        $this->_object->expects($this->never())->method('_setConfigValue');
        $this->_object->initStoreAfter();
    }

    /**
     * @magentoAdminConfigFixture any_config some_value
     */
    public function testInitStoreAfter()
    {
        $this->_object->startTest($this);
        $this->_object->expects(
            $this->at(0)
        )->method(
            '_getConfigValue'
        )->with(
            'any_config'
        )->will(
            $this->returnValue('some_value')
        );
        $this->_object->expects($this->at(1))->method('_setConfigValue')->with('any_config', 'some_value');
        $this->_object->initStoreAfter();
    }
}
