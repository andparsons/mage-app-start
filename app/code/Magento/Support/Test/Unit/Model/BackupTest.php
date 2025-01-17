<?php
namespace Magento\Support\Test\Unit\Model;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\Support\Model\Backup;
use Magento\Support\Console\Command\UtilityCheckCommand;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Support\Model\Backup
     */
    protected $backupModel;

    /**
     * @var \Magento\Support\Model\Backup\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backupConfigMock;

    /**
     * @var \Magento\Support\Helper\Shell|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shellHelperMock;

    /**
     * @var \Magento\Support\Model\Backup\Cmd\PhpFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmdPhpFactoryMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->backupConfigMock = $this->createMock(\Magento\Support\Model\Backup\Config::class);
        $this->shellHelperMock = $this->createMock(\Magento\Support\Helper\Shell::class);
        $this->cmdPhpFactoryMock = $this->getMockBuilder(\Magento\Support\Model\Backup\Cmd\PhpFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);

        /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)
            ->willReturn($this->directoryMock);

        $this->backupModel = $this->objectManagerHelper->getObject(
            \Magento\Support\Model\Backup::class,
            [
                'backupConfig' => $this->backupConfigMock,
                'shellHelper' => $this->shellHelperMock,
                'cmdPhpFactory' => $this->cmdPhpFactoryMock,
                'filesystem' => $filesystem
            ]
        );
    }

    /**
     * @param \Magento\Support\Model\Backup\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $setItem
     * @param string|null $setKeyItem
     * @param string|int $getKeyItem
     * @param \Magento\Support\Model\Backup\AbstractItem|\PHPUnit_Framework_MockObject_MockObject|false $expectedResult
     * @return void
     * @dataProvider addAndGetItemDataProvider
     */
    public function testAddAndGetItem($setItem, $setKeyItem, $getKeyItem, $expectedResult)
    {
        $this->assertSame($this->backupModel, $this->backupModel->addItem($setItem, $setKeyItem));
        $this->assertSame($expectedResult, $this->backupModel->getItem($getKeyItem));
    }

    /**
     * @return array
     */
    public function addAndGetItemDataProvider()
    {
        $itemMock = $this->getAbstractItemMock();

        return [
            [
                'setItem' => $itemMock,
                'setKeyItem' => 'testKey',
                'getKeyItem' => 'testKey',
                'expectedResult' => $itemMock
            ],
            [
                'setItem' => $itemMock,
                'setKeyItem' => null,
                'getKeyItem' => 'testKey',
                'expectedResult' => false
            ],
            [
                'setItem' => $itemMock,
                'setKeyItem' => null,
                'getKeyItem' => 0,
                'expectedResult' => $itemMock
            ],
        ];
    }

    /**
     * @param \Magento\Support\Model\Backup\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $setItem
     * @param string|null $setKeyItem
     * @param $expectedResult
     * @return void
     * @dataProvider addItemAndGetItemsDataProvider
     */
    public function testAddItemAndGetItems($setItem, $setKeyItem, $expectedResult)
    {
        $this->assertSame($this->backupModel, $this->backupModel->addItem($setItem, $setKeyItem));
        $this->assertSame($expectedResult, $this->backupModel->getItems());
    }

    /**
     * @return array
     */
    public function addItemAndGetItemsDataProvider()
    {
        $itemMock = $this->getAbstractItemMock();

        return [
            [
                'setItem' => $itemMock,
                'setKeyItem' => 'testKey',
                'expectedResult' => ['testKey' => $itemMock]
            ],
            [
                'setItem' => $itemMock,
                'setKeyItem' => null,
                'expectedResult' => [$itemMock]
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetItemsFromBackupConfig()
    {
        $resourceItemMock = $this->createMock(\Magento\Support\Model\ResourceModel\Backup\Item::class);

        $itemMock = $this->getAbstractItemMock();
        $itemMock->expects($this->once())
            ->method('getType')
            ->willReturn(1);
        $itemMock->expects($this->once())
            ->method('loadItemByBackupIdAndType')
            ->with(null, 1)
            ->willReturn($resourceItemMock);
        $itemMock->expects($this->once())
            ->method('setBackup')
            ->with($this->backupModel);

        $itemsFromConfig = [
            'testKey' => $itemMock
        ];

        $this->backupConfigMock->expects($this->once())
            ->method('getBackupItems')
            ->willReturn($itemsFromConfig);

        $this->assertSame($itemsFromConfig, $this->backupModel->getItems());
    }

    /**
     * @param string $unsupportedOs
     * @param bool $execEnabled
     * @return void
     */
    protected function initCheckOsAndExecForValidateTest($unsupportedOs = '', $execEnabled = true)
    {
        $this->backupConfigMock->expects($this->any())
            ->method('getUnsupportedOs')
            ->willReturn($unsupportedOs);
        $this->shellHelperMock->expects($this->any())
            ->method('isExecEnabled')
            ->willReturn($execEnabled);
    }

    /**
     * @param string $pathPhp
     * @param string $scriptName
     * @param string $resultGenerate
     * @return void
     */
    protected function initCmdPhpForValidateTest($pathPhp, $scriptName, $resultGenerate)
    {
        $this->shellHelperMock->expects($this->once())
            ->method('getUtility')
            ->with(\Magento\Support\Helper\Shell::UTILITY_PHP)
            ->willReturn($pathPhp);

        /** @var \Magento\Support\Model\Backup\Cmd\Php|\PHPUnit_Framework_MockObject_MockObject $cmdPhpMock */
        $cmdPhpMock = $this->createMock(\Magento\Support\Model\Backup\Cmd\Php::class);
        $cmdPhpMock->expects($this->once())
            ->method('setScriptInterpreter')
            ->with($pathPhp);
        $cmdPhpMock->expects($this->once())
            ->method('setScriptName')
            ->with($scriptName);
        $cmdPhpMock->expects($this->once())
            ->method('generate')
            ->willReturn($resultGenerate);
        $this->cmdPhpFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cmdPhpMock);
    }

    /**
     * @param string $itemValidateResult
     * @param string $cmdExecuteResult
     * @param array $expectedResult
     * @return void
     * @dataProvider validateDataProvider
     */
    public function testValidate($itemValidateResult, $cmdExecuteResult, $expectedResult)
    {
        $pathPhp = '/bin/php';
        $scriptName = 'bin/magento support:utility:check --'. UtilityCheckCommand::INPUT_KEY_HIDE_PATHS;
        $resultGenerate = $pathPhp . ' ' . $scriptName;

        $itemMock = $this->getAbstractItemMock();
        $itemMock->expects($this->once())
            ->method('validate')
            ->willReturn($itemValidateResult);
        $this->backupModel->addItem($itemMock);

        $this->initCheckOsAndExecForValidateTest();
        $this->initCmdPhpForValidateTest($pathPhp, $scriptName, $resultGenerate);

        $this->shellHelperMock->expects($this->once())
            ->method('execute')
            ->with($resultGenerate)
            ->willReturn($cmdExecuteResult);

        $this->assertSame($expectedResult, $this->backupModel->validate());
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                'itemValidateResult' => '',
                'cmdExecuteResult' => '',
                'expectedResult' => []
            ],
            [
                'itemValidateResult' => 'Item is not valid',
                'cmdExecuteResult' => 'Command is not valid',
                'expectedResult' => [
                    'Item is not valid',
                    'Command is not valid'
                ]
            ],
        ];
    }

    /**
     * @return void
     */
    public function testValidateUnsupportedOs()
    {
        $unsupportedOs = 'Windows';
        $errorMsg = sprintf(__("Support Module doesn't support %s operation system"), $unsupportedOs);
        $this->initCheckOsAndExecForValidateTest($unsupportedOs);

        $this->assertEquals([$errorMsg], $this->backupModel->validate());
    }

    /**
     * @return void
     */
    public function testValidateExecIsDisabled()
    {
        $errorMsg = __('Unable to create backup due to php exec function is disabled');
        $this->initCheckOsAndExecForValidateTest('', false);

        $this->assertEquals([$errorMsg], $this->backupModel->validate());
    }

    /**
     * @return void
     */
    public function testRun()
    {
        $pathPhp = '/bin/php';
        $scriptName = 'bin/magento support:utility:check --' . UtilityCheckCommand::INPUT_KEY_HIDE_PATHS;
        $resultGenerate = $pathPhp . ' ' . $scriptName;
        $cmd = '/bin/php bin/magento support:test:test';

        $itemMock = $this->getAbstractItemMock();
        $itemMock->expects($this->once())
            ->method('validate')
            ->willReturn('');
        $itemMock->expects($this->once())
            ->method('getCmd')
            ->willReturn($cmd);
        $this->backupModel->addItem($itemMock);

        $this->initCheckOsAndExecForValidateTest();
        $this->initCmdPhpForValidateTest($pathPhp, $scriptName, $resultGenerate, '');
        $this->shellHelperMock->expects($this->any())
            ->method('execute')
            ->willReturnMap([
                [$resultGenerate, [], ''],
                ['(/bin/php bin/magento support:test:test) > backup.log 2>/dev/null &', [], ''],
            ]);

        $this->backupModel->run();
    }

    /**
     * @return void
     */
    public function testRunThrowsStateException()
    {
        $unsupportedOs = 'Windows';
        $errorMsg = "Support Module doesn't support " . $unsupportedOs . " operation system";
        $this->initCheckOsAndExecForValidateTest($unsupportedOs);

        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage($errorMsg);

        $this->backupModel->run();
    }

    /**
     * @return void
     */
    public function testAfterSave()
    {
        $itemMock = $this->getAbstractItemMock();
        $itemMock->expects($this->once())
            ->method('setBackupId')
            ->with(null);
        $itemMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->backupModel->addItem($itemMock);

        $this->assertSame($this->backupModel, $this->backupModel->afterSave());
    }

    /**
     * @return void
     */
    public function testAfterDelete()
    {
        $paths = $this->generalDeleteTest(true, true, true, true);
        $this->directoryMock->expects($this->any())
            ->method('delete')
            ->willReturnMap([
                [$paths['itemFile'], true],
                [$paths['logPath'], true]
            ]);

        $this->assertSame($this->backupModel, $this->backupModel->afterDelete());
    }

    /**
     * @param bool $itemExists
     * @param bool $itemWritable
     * @param bool $logExists
     * @param bool $logWritable
     * @return void
     * @dataProvider afterDeleteNeverDeleteDataProvider
     */
    public function testAfterDeleteNeverDelete($itemExists, $itemWritable, $logExists, $logWritable)
    {
        $this->generalDeleteTest($itemExists, $itemWritable, $logExists, $logWritable);
        $this->directoryMock->expects($this->never())
            ->method('delete');

        $this->assertSame($this->backupModel, $this->backupModel->afterDelete());
    }

    /**
     * @return array
     */
    public function afterDeleteNeverDeleteDataProvider()
    {
        return [
            [
                'itemExists' => true,
                'itemWritable' => false,
                'logExists' => true,
                'logWritable' => false
            ],
            [
                'itemExists' => false,
                'itemWritable' => true,
                'logExists' => false,
                'logWritable' => true
            ],
            [
                'itemExists' => false,
                'itemWritable' => false,
                'logExists' => false,
                'logWritable' => false
            ],
        ];
    }

    /**
     * @param bool $itemExists
     * @param bool $itemWritable
     * @param bool $logExists
     * @param bool $logWritable
     * @return array
     */
    protected function generalDeleteTest($itemExists, $itemWritable, $logExists, $logWritable)
    {
        $itemName = 'nameBackupItem';
        $itemFile = '/path/' . $itemName;
        $logPath = '/path/' . Backup::LOG_FILENAME;

        $itemMock = $this->getAbstractItemMock();
        $itemMock->expects($this->once())
            ->method('getName')
            ->willReturn($itemName);
        $this->backupModel->addItem($itemMock);

        $this->shellHelperMock->expects($this->any())
            ->method('getFilePath')
            ->willReturnMap([
                [$itemName, $itemFile],
                [Backup::LOG_FILENAME, $logPath]
            ]);
        $this->directoryMock->expects($this->any())
            ->method('isExist')
            ->willReturnMap([
                [$itemFile, $itemExists],
                [$logPath, $logExists]
            ]);
        $this->directoryMock->expects($this->any())
            ->method('isWritable')
            ->willReturnMap([
                [$itemFile, $itemWritable],
                [$logPath, $logWritable]
            ]);

        return [
            'itemFile' => $itemFile,
            'logPath' => $logPath
        ];
    }

    /**
     * @return \Magento\Support\Model\Backup\AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAbstractItemMock()
    {
        /** @var \Magento\Support\Model\Backup\AbstractItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMockBuilder(\Magento\Support\Model\Backup\AbstractItem::class)
            ->setMethods([
                'loadItemByBackupIdAndType', 'getType', 'setBackup',
                'validate', 'getCmd', 'save', 'setBackupId', 'getName'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $item;
    }

    /**
     * @return void
     */
    public function testGetAvailableStatuses()
    {
        $expectedResult = [
            Backup::STATUS_PROCESSING => __('Incomplete'),
            Backup::STATUS_COMPLETE => __('Complete'),
            Backup::STATUS_FAILED => __('Failed')
        ];

        $this->assertEquals($expectedResult, $this->backupModel->getAvailableStatuses());
    }
}
