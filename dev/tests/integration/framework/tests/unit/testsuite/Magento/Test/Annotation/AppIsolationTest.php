<?php

/**
 * Test class for \Magento\TestFramework\Annotation\AppIsolation.
 */
namespace Magento\Test\Annotation;

class AppIsolationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\AppIsolation
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_application;

    protected function setUp()
    {
        $this->_application = $this->createPartialMock(\Magento\TestFramework\Application::class, ['reinitialize']);
        $this->_object = new \Magento\TestFramework\Annotation\AppIsolation($this->_application);
    }

    protected function tearDown()
    {
        $this->_application = null;
        $this->_object = null;
    }

    public function testStartTestSuite()
    {
        $this->_application->expects($this->once())->method('reinitialize');
        $this->_object->startTestSuite();
    }

    /**
     * @magentoAppIsolation invalid
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEndTestIsolationInvalid()
    {
        $this->_object->endTest($this);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppIsolation disabled
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEndTestIsolationAmbiguous()
    {
        $this->_object->endTest($this);
    }

    public function testEndTestIsolationDefault()
    {
        $this->_application->expects($this->never())->method('reinitialize');
        $this->_object->endTest($this);
    }

    public function testEndTestIsolationController()
    {
        /** @var $controllerTest \Magento\TestFramework\TestCase\AbstractController */
        $controllerTest = $this->getMockForAbstractClass(\Magento\TestFramework\TestCase\AbstractController::class);
        $this->_application->expects($this->once())->method('reinitialize');
        $this->_object->endTest($controllerTest);
    }

    /**
     * @magentoAppIsolation disabled
     */
    public function testEndTestIsolationDisabled()
    {
        $this->_application->expects($this->never())->method('reinitialize');
        $this->_object->endTest($this);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testEndTestIsolationEnabled()
    {
        $this->_application->expects($this->once())->method('reinitialize');
        $this->_object->endTest($this);
    }
}
