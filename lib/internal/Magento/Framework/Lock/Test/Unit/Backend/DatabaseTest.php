<?php
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Lock\Backend\Database;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @inheritdoc
 */
class DatabaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend_Db_Statement_Interface
     */
    private $statement;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statement = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection->expects($this->any())
            ->method('query')
            ->willReturn($this->statement);

        $this->objectManager = new ObjectManager($this);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Database $database */
        $this->database = $this->objectManager->getObject(
            Database::class,
            [
                'resource' => $this->resource,
                'deploymentConfig' => $this->deploymentConfig,
            ]
        );
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testLock()
    {
        $this->deploymentConfig
            ->method('isDbAvailable')
            ->with()
            ->willReturn(true);
        $this->statement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(true);

        $this->assertTrue($this->database->lock('testLock'));
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Db_Statement_Exception
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testlockWithTooLongName()
    {
        $this->deploymentConfig
            ->method('isDbAvailable')
            ->with()
            ->willReturn(true);
        $this->database->lock('BbXbyf9rIY5xuAVdviQJmh76FyoeeVHTDpcjmcImNtgpO4Hnz4xk76ZGEyYALvrQu');
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Db_Statement_Exception
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testlockWithAlreadyAcquiredLockInSameSession()
    {
        $this->deploymentConfig
            ->method('isDbAvailable')
            ->with()
            ->willReturn(true);
        $this->statement->expects($this->any())
            ->method('fetchColumn')
            ->willReturn(true);

        $this->database->lock('testLock');
        $this->database->lock('differentLock');
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testLockWithUnavailableDeploymentConfig()
    {
        $this->deploymentConfig
            ->expects($this->atLeast(1))
            ->method('isDbAvailable')
            ->with()
            ->willReturn(false);
        $this->assertTrue($this->database->lock('testLock'));
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testUnlockWithUnavailableDeploymentConfig()
    {
        $this->deploymentConfig
            ->expects($this->atLeast(1))
            ->method('isDbAvailable')
            ->with()
            ->willReturn(false);
        $this->assertTrue($this->database->unlock('testLock'));
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Db_Statement_Exception
     */
    public function testIsLockedWithUnavailableDB()
    {
        $this->deploymentConfig
            ->expects($this->atLeast(1))
            ->method('isDbAvailable')
            ->with()
            ->willReturn(false);
        $this->assertFalse($this->database->isLocked('testLock'));
    }
}
