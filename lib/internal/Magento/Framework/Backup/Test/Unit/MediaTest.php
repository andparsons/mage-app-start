<?php
namespace Magento\Framework\Backup\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

require_once __DIR__ . '/_files/io.php';

class MediaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\Backup\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backupFactoryMock;

    /**
     * @var \Magento\Framework\Backup\Db
     */
    protected $_backupDbMock;

    /**
     * @var \Magento\Framework\Backup\Filesystem\Rollback\Fs
     */
    private $fsMock;

    public static function setUpBeforeClass()
    {
        require __DIR__ . '/_files/app_dirs.php';
    }

    public static function tearDownAfterClass()
    {
        require __DIR__ . '/_files/app_dirs_rollback.php';
    }

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->_backupDbMock = $this->createMock(\Magento\Framework\Backup\Db::class);
        $this->_backupDbMock->expects($this->any())->method('setBackupExtension')->will($this->returnSelf());

        $this->_backupDbMock->expects($this->any())->method('setTime')->will($this->returnSelf());

        $this->_backupDbMock->expects($this->any())->method('setBackupsDir')->will($this->returnSelf());

        $this->_backupDbMock->expects($this->any())->method('setResourceModel')->will($this->returnSelf());

        $this->_backupDbMock->expects(
            $this->any()
        )->method(
            'getBackupPath'
        )->will(
            $this->returnValue('\unexistingpath')
        );

        $this->_backupDbMock->expects($this->any())->method('create')->will($this->returnValue(true));

        $this->_filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $dirMock = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $this->_filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($dirMock));

        $this->_backupFactoryMock = $this->createMock(\Magento\Framework\Backup\Factory::class);
        $this->_backupFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_backupDbMock)
        );

        $this->fsMock = $this->createMock(\Magento\Framework\Backup\Filesystem\Rollback\Fs::class);
    }

    /**
     * @param string $action
     * @dataProvider actionProvider
     */
    public function testAction($action)
    {
        $this->_backupFactoryMock->expects($this->once())->method('create');

        $rootDir = str_replace('\\', '/', TESTS_TEMP_DIR) . '/Magento/Backup/data';

        $model = $this->objectManager->getObject(
            \Magento\Framework\Backup\Media::class,
            [
                'filesystem' => $this->_filesystemMock,
                'backupFactory' => $this->_backupFactoryMock,
                'rollBackFs' => $this->fsMock,
            ]
        );
        $model->setRootDir($rootDir);
        $model->setBackupsDir($rootDir);
        $model->{$action}();
        $this->assertTrue($model->getIsSuccess());

        $this->assertTrue($model->{$action}());

        $ignorePaths = $model->getIgnorePaths();

        $expected = [
            $rootDir,
            $rootDir . '/app',
            $rootDir . '/var/log',
        ];
        $ignored = array_intersect($expected, $ignorePaths);
        sort($ignored);
        $this->assertEquals($expected, $ignored);
    }

    /**
     * @return array
     */
    public static function actionProvider()
    {
        return [['create'], ['rollback']];
    }
}
