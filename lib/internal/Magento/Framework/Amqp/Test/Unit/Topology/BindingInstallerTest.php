<?php
namespace Magento\Framework\Amqp\Test\Unit\Topology;

use Magento\Framework\Amqp\Topology\BindingInstaller;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

class BindingInstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testInstall()
    {
        $installerOne = $this->createMock(BindingInstallerInterface::class);
        $installerTwo = $this->createMock(BindingInstallerInterface::class);
        $model = new BindingInstaller(
            [
                'queue' => $installerOne,
                'exchange' => $installerTwo,
            ]
        );
        $channel = $this->createMock(AMQPChannel::class);
        $binding = $this->createMock(BindingInterface::class);
        $binding->expects($this->once())->method('getDestinationType')->willReturn('queue');
        $installerOne->expects($this->once())->method('install')->with($channel, $binding, 'magento');
        $installerTwo->expects($this->never())->method('install');
        $model->install($channel, $binding, 'magento');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Installer type [test] is not configured
     */
    public function testInstallInvalidType()
    {
        $installerOne = $this->createMock(BindingInstallerInterface::class);
        $installerTwo = $this->createMock(BindingInstallerInterface::class);
        $model = new BindingInstaller(
            [
                'queue' => $installerOne,
                'exchange' => $installerTwo,
            ]
        );
        $channel = $this->createMock(AMQPChannel::class);
        $binding = $this->createMock(BindingInterface::class);
        $binding->expects($this->once())->method('getDestinationType')->willReturn('test');
        $installerOne->expects($this->never())->method('install');
        $installerTwo->expects($this->never())->method('install');
        $model->install($channel, $binding, 'magento');
    }
}
