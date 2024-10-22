<?php
namespace Magento\MediaStorage\Test\Unit\Model\File\Storage\Directory;

use Magento\MediaStorage\Model\ResourceModel\File\Storage\Directory\Database;

/**
 * Class DatabaseTest
 */
class DatabaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\Database |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryDatabase;

    /**
     * @var \Magento\Framework\Model\Context |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperStorageDatabase;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateModelMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\Database |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var Database |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceDirectoryDatabaseMock;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    /**
     * @var string
     */
    protected $customConnectionName = 'custom-connection-name';

    /**
     * Setup preconditions
     */
    protected function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->helperStorageDatabase = $this->createMock(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $this->dateModelMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $this->directoryMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\Directory\Database::class,
            ['setPath', 'setName', '__wakeup', 'save', 'getParentId']
        );
        $this->directoryFactoryMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory::class,
            ['create']
        );
        $this->resourceDirectoryDatabaseMock = $this->createMock(
            \Magento\MediaStorage\Model\ResourceModel\File\Storage\Directory\Database::class
        );
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->directoryFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->directoryMock)
        );

        $this->configMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->configMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            \Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA_DATABASE,
            'default'
        )->will(
            $this->returnValue($this->customConnectionName)
        );

        $this->contextMock->expects($this->once())->method('getLogger')->will($this->returnValue($this->loggerMock));

        $this->directoryDatabase = new \Magento\MediaStorage\Model\File\Storage\Directory\Database(
            $this->contextMock,
            $this->registryMock,
            $this->helperStorageDatabase,
            $this->dateModelMock,
            $this->configMock,
            $this->directoryFactoryMock,
            $this->resourceDirectoryDatabaseMock,
            null,
            $this->customConnectionName,
            []
        );
    }

    /**
     * test import directories
     */
    public function testImportDirectories()
    {
        $this->directoryMock->expects($this->any())->method('getParentId')->will($this->returnValue(1));
        $this->directoryMock->expects($this->any())->method('save');

        $this->directoryMock->expects(
            $this->exactly(2)
        )->method(
            'setPath'
        )->with(
            $this->logicalOr($this->equalTo('path/number/one'), $this->equalTo('path/number/two'))
        );

        $this->directoryDatabase->importDirectories(
            [
                ['name' => 'first', 'path' => './path/number/one'],
                ['name' => 'second', 'path' => './path/number/two'],
            ]
        );
    }

    /**
     * test import directories without parent
     */
    public function testImportDirectoriesFailureWithoutParent()
    {
        $this->directoryMock->expects($this->any())->method('getParentId')->will($this->returnValue(null));

        $this->loggerMock->expects($this->any())->method('critical');

        $this->directoryDatabase->importDirectories([]);
    }

    /**
     * test import directories not an array
     */
    public function testImportDirectoriesFailureNotArray()
    {
        $this->directoryMock->expects($this->never())->method('getParentId')->will($this->returnValue(null));

        $this->directoryDatabase->importDirectories('not an array');
    }

    public function testSetGetConnectionName()
    {
        $this->assertSame($this->customConnectionName, $this->directoryDatabase->getConnectionName());
        $this->directoryDatabase->setConnectionName('test');
        $this->assertSame('test', $this->directoryDatabase->getConnectionName());
        $this->directoryDatabase->unsetData();
        $this->assertSame('test', $this->directoryDatabase->getConnectionName());
    }
}
