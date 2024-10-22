<?php
namespace Magento\Setup\Test\Unit\Module;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\ConnectionFactory;

class ConnectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $serviceLocatorMock = $this->createMock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $objectManagerProviderMock = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $serviceLocatorMock->expects($this->once())
            ->method('get')
            ->with(
                \Magento\Setup\Model\ObjectManagerProvider::class
            )
            ->willReturn($objectManagerProviderMock);
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerProviderMock->expects($this->once())
            ->method('get')
            ->willReturn($objectManagerMock);
        $this->connectionFactory = $objectManager->getObject(
            ConnectionFactory::class,
            [
                'serviceLocator' => $serviceLocatorMock
            ]
        );
    }

    /**
     * @param array $config
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage MySQL adapter: Missing required configuration option 'host'
     * @dataProvider createDataProvider
     */
    public function testCreate($config)
    {
        $this->connectionFactory->create($config);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [
                []
            ],
            [
                ['value']
            ],
            [
                ['active' => 0]
            ],
        ];
    }
}
