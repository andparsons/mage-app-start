<?php
namespace Magento\Framework\App\ResourceConnection;

class ConnectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\ConnectionFactory
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\App\ResourceConnection\ConnectionFactory(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        );
    }

    public function testCreate()
    {
        $dbInstance = \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        $dbConfig = [
            'host' => $dbInstance->getHost(),
            'username' => $dbInstance->getUser(),
            'password' => $dbInstance->getPassword(),
            'dbname' => $dbInstance->getSchema(),
            'active' => true,
        ];
        $connection = $this->model->create($dbConfig);
        $this->assertInstanceOf(\Magento\Framework\DB\Adapter\AdapterInterface::class, $connection);
        $this->assertAttributeInstanceOf(\Magento\Framework\DB\LoggerInterface::class, 'logger', $connection);
    }
}
