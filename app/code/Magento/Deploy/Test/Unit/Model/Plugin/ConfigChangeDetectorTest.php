<?php

namespace Magento\Deploy\Test\Unit\Model\Plugin;

use Magento\Deploy\Model\Plugin\ConfigChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;

class ConfigChangeDetectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigChangeDetector
     */
    private $configChangeDetectorPlugin;

    /**
     * @var ChangeDetector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $changeDetectorMock;

    /**
     * @var FrontControllerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontControllerMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->changeDetectorMock = $this->getMockBuilder(ChangeDetector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontControllerMock = $this->getMockBuilder(FrontControllerInterface::class)
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->configChangeDetectorPlugin = new ConfigChangeDetector($this->changeDetectorMock);
    }

    /**
     * @return void
     */
    public function testBeforeDispatchWithoutException()
    {
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(false);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage The configuration file has changed. Run the "app:config:import" or the "setup:upgrade" command to synchronize the configuration.
     * @codingStandardsIgnoreEnd
     */
    public function testBeforeDispatchWithException()
    {
        $this->changeDetectorMock->expects($this->once())
            ->method('hasChanges')
            ->willReturn(true);
        $this->configChangeDetectorPlugin->beforeDispatch($this->frontControllerMock, $this->requestMock);
    }
}
