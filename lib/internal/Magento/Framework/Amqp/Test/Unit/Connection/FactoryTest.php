<?php
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit\Connection;

use Magento\Framework\Amqp\Connection\Factory;
use Magento\Framework\Amqp\Connection\FactoryOptions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Tests \Magento\Framework\Amqp\Connection\Factory.
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Factory
     */
    private $object;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManagerInterface;

    /**
     * @var FactoryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $className = \Magento\Framework\ObjectManagerInterface::class;
        $this->objectManagerInterface = $this->createMock($className);

        $this->optionsMock = $this->getMockBuilder(FactoryOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'isSslEnabled',
                    'getHost',
                    'getPort',
                    'getUsername',
                    'getPassword',
                    'getVirtualHost',
                    'getSslOptions',
                ]
            )
            ->getMock();

        $this->object = $this->objectManager->getObject(Factory::class);
    }

    /**
     * @param bool $sslEnabled
     * @param string $connectionClass
     * @return void
     * @dataProvider connectionDataProvider
     */
    public function testSSLConnection($sslEnabled, $connectionClass)
    {
        $this->optionsMock->expects($this->exactly(2))
            ->method('isSslEnabled')
            ->willReturn($sslEnabled);
        $this->optionsMock->expects($this->once())
            ->method('getHost')
            ->willReturn('127.0.0.1');
        $this->optionsMock->expects($this->once())
            ->method('getPort')
            ->willReturn('5672');
        $this->optionsMock->expects($this->once())
            ->method('getUsername')
            ->willReturn('guest');
        $this->optionsMock->expects($this->once())
            ->method('getPassword')
            ->willReturn('guest');
        $this->optionsMock->expects($this->exactly(2))
            ->method('getVirtualHost')
            ->willReturn('/');
        $this->optionsMock->expects($this->any())
            ->method('getSslOptions')
            ->willReturn(null);

        $this->objectManagerInterface->expects($this->any())
            ->method('create')
            ->with($connectionClass)
            ->willReturn($this->createMock($connectionClass));

        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerInterface);

        $connection = $this->object->create($this->optionsMock);

        $this->assertInstanceOf($connectionClass, $connection);
    }

    /**
     * @return array
     */
    public function connectionDataProvider()
    {
        return [
            [
                'ssl_enabled' => true,
                'connection_class' => AMQPSSLConnection::class,
            ],
            [
                'ssl_enabled' => false,
                'connection_class' => AMQPStreamConnection::class,
            ],
        ];
    }

    protected function tearDown()
    {
        $this->objectManager->setBackwardCompatibleProperty(
            null,
            '_instance',
            null,
            \Magento\Framework\App\ObjectManager::class
        );
    }
}
