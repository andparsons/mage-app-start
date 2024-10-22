<?php

namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Env;

use Magento\Framework\MessageQueue\Consumer\Config\Env\Reader;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Consumer\Config\Env\Reader
     */
    private $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $envConfig;

    protected function setUp()
    {
        $this->envConfig =
            $this->getMockBuilder(\Magento\Framework\MessageQueue\Config\Reader\Env::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader = new Reader($this->envConfig);
    }

    public function testRead()
    {
        $configData['consumers'] = ['consumerConfig'];
        $this->envConfig->expects($this->once())->method('read')->willReturn($configData);
        $actual = $this->reader->read();
        $this->assertEquals(['consumerConfig'], $actual);
    }

    public function testReadIfConsumerConfigNotExist()
    {
        $this->envConfig->expects($this->once())->method('read')->willReturn([]);
        $actual = $this->reader->read();
        $this->assertEquals([], $actual);
    }
}
