<?php
namespace Magento\Support\Test\Unit\Helper;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Support\Helper\Shell as ShellHelper;

class ShellTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Support\Helper\Shell
     */
    protected $shellHelper;

    /**
     * @var \Magento\Framework\ShellInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shellMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $path = '/some_path';

    /**
     * @var string
     */
    protected $absolutePath = '/var/www/test/some_path';

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var array
     */
    protected $pathsMap = [];

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->shellMock = $this->createMock(\Magento\Framework\ShellInterface::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->directoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);

        /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->atLeastOnce())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)
            ->willReturn($this->directoryMock);

        $this->context = $this->objectManagerHelper->getObject(
            \Magento\Framework\App\Helper\Context::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
        $this->shellHelper = $this->objectManagerHelper->getObject(
            \Magento\Support\Helper\Shell::class,
            [
                'context' => $this->context,
                'shell' => $this->shellMock,
                'filesystem' => $filesystem
            ]
        );

        $this->paths = [
            ShellHelper::UTILITY_GZIP => '/path/' . ShellHelper::UTILITY_GZIP,
            ShellHelper::UTILITY_LSOF => '/path/' . ShellHelper::UTILITY_LSOF,
            ShellHelper::UTILITY_MYSQLDUMP => '/path/' . ShellHelper::UTILITY_MYSQLDUMP,
            ShellHelper::UTILITY_NICE => '/path/' . ShellHelper::UTILITY_NICE,
            ShellHelper::UTILITY_PHP => '/path/' . ShellHelper::UTILITY_PHP,
            ShellHelper::UTILITY_TAR => '/path/' . ShellHelper::UTILITY_TAR,
            ShellHelper::UTILITY_SED => '/path/' . ShellHelper::UTILITY_SED,
            ShellHelper::UTILITY_BASH => '/path/' . ShellHelper::UTILITY_BASH,
            ShellHelper::UTILITY_MYSQL => '/path/' . ShellHelper::UTILITY_MYSQL
        ];

        $this->pathsMap = [
            ['which ' . ShellHelper::UTILITY_GZIP, [], $this->paths[ShellHelper::UTILITY_GZIP]],
            ['which ' . ShellHelper::UTILITY_LSOF, [], $this->paths[ShellHelper::UTILITY_LSOF]],
            ['which ' . ShellHelper::UTILITY_MYSQLDUMP, [], $this->paths[ShellHelper::UTILITY_MYSQLDUMP]],
            ['which ' . ShellHelper::UTILITY_NICE, [], $this->paths[ShellHelper::UTILITY_NICE]],
            ['which ' . ShellHelper::UTILITY_PHP, [], $this->paths[ShellHelper::UTILITY_PHP]],
            ['which ' . ShellHelper::UTILITY_TAR, [], $this->paths[ShellHelper::UTILITY_TAR]],
            ['which ' . ShellHelper::UTILITY_SED, [], $this->paths[ShellHelper::UTILITY_SED]],
            ['which ' . ShellHelper::UTILITY_BASH, [], $this->paths[ShellHelper::UTILITY_BASH]],
            ['which ' . ShellHelper::UTILITY_MYSQL, [], $this->paths[ShellHelper::UTILITY_MYSQL]],
        ];
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $command = 'ls';
        $result = '/filename.jpg';

        $this->shellMock->expects($this->once())
            ->method('execute')
            ->with($command, [])
            ->willReturn($result);

        $this->assertSame($result, $this->shellHelper->execute($command));
    }

    /**
     * @param string $path
     * @param bool $pathExists
     * @return void
     */
    protected function initTestOutputPath($path, $pathExists = true)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(ShellHelper::XML_OUTPUT_PATH)
            ->willReturn($path);
        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->with($path)
            ->willReturn($pathExists);
        $this->directoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->with($path)
            ->willReturn($this->absolutePath);
    }

    /**
     * @return void
     */
    public function testGetOutputPath()
    {
        $this->initTestOutputPath($this->path);
        $this->directoryMock->expects($this->never())
            ->method('create');

        $this->assertSame($this->path, $this->shellHelper->getOutputPath());
    }

    /**
     * @return void
     */
    public function testGetOutputPathDoesNotExist()
    {
        $this->initTestOutputPath($this->path, false);
        $this->directoryMock->expects($this->once())
            ->method('create')
            ->with($this->path);

        $this->assertSame($this->path, $this->shellHelper->getOutputPath());
    }

    /**
     * @return void
     */
    public function testGetAbsoluteOutputPath()
    {
        $this->initTestOutputPath($this->path, true);

        $this->assertSame($this->absolutePath, $this->shellHelper->getAbsoluteOutputPath());
    }

    /**
     * @return void
     */
    public function testGetFilePath()
    {
        $fileName = '/test.jpg';
        $this->initTestOutputPath($this->path);

        $this->assertSame($this->path . $fileName, $this->shellHelper->getFilePath($fileName));
    }

    /**
     * @return void
     */
    public function testGetPathsFileName()
    {
        $this->initTestOutputPath($this->path);

        $this->assertSame($this->path . '/' . ShellHelper::PATHS_FILE, $this->shellHelper->getPathsFileName());
    }

    /**
     * @param array $pathsMap
     * @return void
     */
    protected function initPathsForTest($pathsMap = [])
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(ShellHelper::XML_OUTPUT_PATH)
            ->willReturn($this->path);
        $this->directoryMock->expects($this->any())
            ->method('isExist')
            ->willReturnMap([
                [$this->path, true],
                [$this->path . '/' . ShellHelper::PATHS_FILE, false]
            ]);

        $this->shellMock->expects($this->any())
            ->method('execute')
            ->willReturnMap($pathsMap);
    }

    /**
     * @return void
     */
    public function testInitPaths()
    {
        $this->initPathsForTest($this->pathsMap);
        $this->shellHelper->initPaths();

        $this->assertSame($this->paths, $this->shellHelper->getUtilities());
    }

    /**
     * @return void
     */
    public function testInitPathsThrowsNotFoundException()
    {
        $this->initPathsForTest();
        $this->shellMock->expects($this->any())
            ->method('execute')
            ->willThrowException(new LocalizedException(__('Error message!')));
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);

        $this->shellHelper->initPaths();
    }

    /**
     * @return void
     */
    public function testGetUtility()
    {
        $this->initPathsForTest($this->pathsMap);
        $this->shellHelper->initPaths();

        $this->assertSame(
            '/path/' . ShellHelper::UTILITY_GZIP,
            $this->shellHelper->getUtility(ShellHelper::UTILITY_GZIP)
        );
    }

    /**
     * @return void
     */
    public function testGetUtilityThrowsNotFoundException()
    {
        $this->initPathsForTest($this->pathsMap);
        $this->shellHelper->initPaths();
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);

        $this->shellHelper->getUtility('SomeUtility');
    }
}
