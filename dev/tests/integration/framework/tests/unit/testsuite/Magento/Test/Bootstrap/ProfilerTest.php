<?php

/**
 * Test class for \Magento\TestFramework\Bootstrap\Profiler.
 */
namespace Magento\Test\Bootstrap;

class ProfilerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap\Profiler
     */
    protected $_object;

    /**
     * @var \Magento\Framework\Profiler\Driver\Standard|PHPUnit\Framework\MockObject_MockObject
     */
    protected $_driver;

    protected function setUp()
    {
        $this->expectOutputString('');
        $this->_driver =
            $this->createPartialMock(\Magento\Framework\Profiler\Driver\Standard::class, ['registerOutput']);
        $this->_object = new \Magento\TestFramework\Bootstrap\Profiler($this->_driver);
    }

    protected function tearDown()
    {
        $this->_driver = null;
        $this->_object = null;
    }

    public function testRegisterFileProfiler()
    {
        $this->_driver->expects(
            $this->once()
        )->method(
            'registerOutput'
        )->with(
            $this->isInstanceOf(\Magento\Framework\Profiler\Driver\Standard\Output\Csvfile::class)
        );
        $this->_object->registerFileProfiler('php://output');
    }

    public function testRegisterBambooProfiler()
    {
        $this->_driver->expects(
            $this->once()
        )->method(
            'registerOutput'
        )->with(
            $this->isInstanceOf(\Magento\TestFramework\Profiler\OutputBamboo::class)
        );
        $this->_object->registerBambooProfiler('php://output', __DIR__ . '/_files/metrics.php');
    }
}
