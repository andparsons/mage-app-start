<?php

/**
 * Test class for \Magento\TestFramework\Bootstrap.
 */
namespace Magento\Test;

class BootstrapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * Setting values required to be specified
     *
     * @var array
     */
    protected $_requiredSettings = [
        'TESTS_INSTALL_CONFIG_FILE' => 'etc/install-config-mysql.php',
    ];

    /**
     * @var \Magento\TestFramework\Bootstrap\Settings|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_settings;

    /**
     * @var \Magento\TestFramework\Bootstrap\Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_envBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\DocBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_docBlockBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\Profiler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_profilerBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\MemoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $memoryFactory;

    /**
     * @var \Magento\Framework\Shell|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    private $application;

    /**
     * @var string
     */
    protected $_integrationTestsDir;

    protected function setUp()
    {
        $this->_integrationTestsDir = realpath(__DIR__ . '/../../../../../../');
        $this->_settings = $this->createMock(\Magento\TestFramework\Bootstrap\Settings::class);
        $this->_envBootstrap = $this->createPartialMock(
            \Magento\TestFramework\Bootstrap\Environment::class,
            ['emulateHttpRequest', 'emulateSession']
        );
        $this->_docBlockBootstrap = $this->getMockBuilder(\Magento\TestFramework\Bootstrap\DocBlock::class)
            ->setMethods(['registerAnnotations'])
            ->setConstructorArgs([__DIR__])
            ->getMock();
        $profilerDriver =
            $this->createPartialMock(\Magento\Framework\Profiler\Driver\Standard::class, ['registerOutput']);
        $this->_profilerBootstrap = $this->getMockBuilder(\Magento\TestFramework\Bootstrap\Profiler::class)
            ->setMethods(['registerFileProfiler', 'registerBambooProfiler'])
            ->setConstructorArgs([$profilerDriver])
            ->getMock();

        $this->_shell = $this->createPartialMock(\Magento\Framework\Shell::class, ['execute']);
        $this->application = $this->createMock(\Magento\TestFramework\Application::class);
        $this->memoryFactory = $this->createMock(\Magento\TestFramework\Bootstrap\MemoryFactory::class);
        $this->_object = new \Magento\TestFramework\Bootstrap(
            $this->_settings,
            $this->_envBootstrap,
            $this->_docBlockBootstrap,
            $this->_profilerBootstrap,
            $this->_shell,
            $this->application,
            $this->memoryFactory
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_settings = null;
        $this->_envBootstrap = null;
        $this->_docBlockBootstrap = null;
        $this->_profilerBootstrap = null;
        $this->_memoryBootstrap = null;
        $this->_shell = null;
    }

    public function testGetApplication()
    {
        $this->assertSame($this->application, $this->_object->getApplication());
    }

    public function testRunBootstrap()
    {
        $this->_envBootstrap->expects($this->once())
            ->method('emulateHttpRequest')
            ->with($this->identicalTo($_SERVER));
        $this->_envBootstrap->expects($this->once())
            ->method('emulateSession')
            ->with($this->identicalTo(isset($_SESSION) ? $_SESSION : null));

        $memUsageLimit = '100B';
        $memLeakLimit = '60B';
        $settingsMap = [
            ['TESTS_MEM_USAGE_LIMIT', 0, $memUsageLimit],
            ['TESTS_MEM_LEAK_LIMIT', 0, $memLeakLimit],
        ];
        $this->_settings->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($settingsMap));
        $memoryBootstrap = $this->createPartialMock(
            \Magento\TestFramework\Bootstrap\Memory::class,
            ['activateStatsDisplaying', 'activateLimitValidation']
        );
        $memoryBootstrap->expects($this->once())->method('activateStatsDisplaying');
        $memoryBootstrap->expects($this->once())->method('activateLimitValidation');
        $this->memoryFactory->expects($this->once())
            ->method('create')
            ->with($memUsageLimit, $memLeakLimit)
            ->will($this->returnValue($memoryBootstrap));

        $this->_docBlockBootstrap->expects($this->once())
            ->method('registerAnnotations')
            ->with($this->isInstanceOf(\Magento\TestFramework\Application::class));

        $this->_profilerBootstrap->expects($this->never())->method($this->anything());

        $this->_object->runBootstrap();
    }

    public function testRunBootstrapProfilerEnabled()
    {
        $memoryBootstrap = $this->createPartialMock(
            \Magento\TestFramework\Bootstrap\Memory::class,
            ['activateStatsDisplaying', 'activateLimitValidation']
        );
        $memoryBootstrap->expects($this->once())->method('activateStatsDisplaying');
        $memoryBootstrap->expects($this->once())->method('activateLimitValidation');
        $this->memoryFactory->expects($this->once())
            ->method('create')
            ->with(0, 0)
            ->will($this->returnValue($memoryBootstrap));

        $settingsMap = [
            ['TESTS_PROFILER_FILE', '', 'profiler.csv'],
            ['TESTS_BAMBOO_PROFILER_FILE', '', 'profiler_bamboo.csv'],
            ['TESTS_BAMBOO_PROFILER_METRICS_FILE', '', 'profiler_metrics.php'],
        ];
        $this->_settings->expects($this->any())
            ->method('getAsFile')
            ->will($this->returnValueMap($settingsMap));
        $this->_profilerBootstrap
            ->expects($this->once())
            ->method('registerFileProfiler')
            ->with("profiler.csv");
        $this->_profilerBootstrap
            ->expects($this->once())
            ->method('registerBambooProfiler')
            ->with("profiler_bamboo.csv", "profiler_metrics.php");
        $this->_object->runBootstrap();
    }
}
