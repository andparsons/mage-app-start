<?php
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Framework\App\DeploymentConfig;

class DataCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImporterPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configImporterPoolMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configImporterPoolMock = $this->getMockBuilder(ImporterPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataCollector = new DataCollector($this->configImporterPoolMock, $this->deploymentConfigMock);
    }

    /**
     * @return void
     */
    public function testGetConfig()
    {
        $sections = ['first', 'second'];
        $this->configImporterPoolMock->expects($this->once())
            ->method('getSections')
            ->willReturn($sections);
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('getConfigData')
            ->willReturnMap([['first', 'some data']]);

        $this->assertSame(['first' => 'some data', 'second' => null], $this->dataCollector->getConfig());
    }

    /**
     * @return void
     */
    public function testGetConfigSpecificSection()
    {
        $this->configImporterPoolMock->expects($this->never())
            ->method('getSections');
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('getConfigData')
            ->willReturnMap([['someSection', 'some data']]);
        $this->assertSame(['someSection' => 'some data'], $this->dataCollector->getConfig('someSection'));
    }
}
