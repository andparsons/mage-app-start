<?php
namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Setup\Model\ModuleRegistryUninstaller;

class ModuleRegistryUninstallerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleList\Loader
     */
    private $loader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Module\DataSetup
     */
    private $dataSetup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    public function setUp()
    {
        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $this->writer = $this->createMock(\Magento\Framework\App\DeploymentConfig\Writer::class);
        $this->loader = $this->createMock(\Magento\Framework\Module\ModuleList\Loader::class);
        $this->dataSetup = $this->createMock(\Magento\Setup\Module\DataSetup::class);
        $dataSetupFactory = $this->createMock(\Magento\Setup\Module\DataSetupFactory::class);
        $dataSetupFactory->expects($this->any())->method('create')->willReturn($this->dataSetup);
        $this->output = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);
        $this->moduleRegistryUninstaller = new ModuleRegistryUninstaller(
            $dataSetupFactory,
            $this->deploymentConfig,
            $this->writer,
            $this->loader
        );
    }

    public function testRemoveModulesFromDb()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->dataSetup->expects($this->atLeastOnce())->method('deleteTableRow');
        $this->moduleRegistryUninstaller->removeModulesFromDb($this->output, ['moduleA', 'moduleB']);
    }

    public function testRemoveModulesFromDeploymentConfig()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->willReturn(['moduleA' => 1, 'moduleB' => 1, 'moduleC' => 1, 'moduleD' => 1]);
        $this->loader->expects($this->once())->method('load')->willReturn(['moduleC' => [], 'moduleD' => []]);
        $this->writer->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_CONFIG => [
                        ConfigOptionsListConstants::KEY_MODULES => ['moduleC' => 1, 'moduleD' => 1]
                    ]
                ]
            );
        $this->moduleRegistryUninstaller->removeModulesFromDeploymentConfig($this->output, ['moduleA', 'moduleB']);
    }
}
