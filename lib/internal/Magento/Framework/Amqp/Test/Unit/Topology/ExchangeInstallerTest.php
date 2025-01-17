<?php
namespace Magento\Framework\Amqp\Test\Unit\Topology;

use Magento\Framework\Amqp\Topology\ExchangeInstaller;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

class ExchangeInstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testInstall()
    {
        $bindingInstaller = $this->createMock(BindingInstallerInterface::class);
        $model = new ExchangeInstaller($bindingInstaller);
        $channel = $this->createMock(AMQPChannel::class);

        $binding = $this->createMock(BindingInterface::class);

        $exchange = $this->createMock(ExchangeConfigItemInterface::class);
        $exchange->expects($this->exactly(2))->method('getName')->willReturn('magento');
        $exchange->expects($this->once())->method('getType')->willReturn('topic');
        $exchange->expects($this->once())->method('isDurable')->willReturn(true);
        $exchange->expects($this->once())->method('isAutoDelete')->willReturn(false);
        $exchange->expects($this->once())->method('isInternal')->willReturn(false);
        $exchange->expects($this->once())->method('getArguments')->willReturn(['some' => 'value']);
        $exchange->expects($this->once())->method('getBindings')->willReturn(['bind01' => $binding]);

        $channel->expects($this->once())
            ->method('exchange_declare')
            ->with('magento', 'topic', false, true, false, false, false, ['some' => ['S', 'value']], null);
        $bindingInstaller->expects($this->once())->method('install')->with($channel, $binding, 'magento');
        $model->install($channel, $exchange);
    }
}
