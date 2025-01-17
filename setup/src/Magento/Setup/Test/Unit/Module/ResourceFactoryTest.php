<?php

namespace Magento\Setup\Test\Unit\Module;

use \Magento\Setup\Module\ResourceFactory;
use \Magento\Setup\Module\ConnectionFactory;

class ResourceFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    protected function setUp()
    {
        $serviceLocatorMock = $this->getMockForAbstractClass(
            \Zend\ServiceManager\ServiceLocatorInterface::class,
            ['get']
        );
        $connectionFactory = new ConnectionFactory($serviceLocatorMock);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(\Magento\Setup\Module\ConnectionFactory::class)
            ->will($this->returnValue($connectionFactory));
        $this->resourceFactory = new ResourceFactory($serviceLocatorMock);
    }

    public function testCreate()
    {
        $resource = $this->resourceFactory->create(
            $this->createMock(\Magento\Framework\App\DeploymentConfig::class)
        );
        $this->assertInstanceOf(\Magento\Framework\App\ResourceConnection::class, $resource);
    }
}
