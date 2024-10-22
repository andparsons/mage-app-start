<?php
namespace Magento\Deploy\Test\Unit\Console\Command\App\SensitiveConfigSet;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorFactory;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorInterface;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SensitiveConfigSetFacade;
use Magento\Deploy\Console\Command\App\SensitiveConfigSetCommand;
use Magento\Deploy\Model\ConfigWriter;
use Magento\Framework\App\Config\CommentParserInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SensitiveConfigSetFacadeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigFilePool|MockObject
     */
    private $configFilePoolMock;

    /**
     * @var CommentParserInterface|MockObject
     */
    private $commentParserMock;

    /**
     * @var ConfigWriter|MockObject
     */
    private $configWriterMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $scopeValidatorMock;

    /**
     * @var CollectorFactory|MockObject
     */
    private $collectorFactoryMock;

    /**
     * @var SensitiveConfigSetFacade
     */
    private $command;

    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->configFilePoolMock = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentParserMock = $this->getMockBuilder(CommentParserInterface::class)
            ->getMockForAbstractClass();
        $this->configWriterMock = $this->getMockBuilder(ConfigWriter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeValidatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->collectorFactoryMock = $this->getMockBuilder(CollectorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();

        $this->command = new SensitiveConfigSetFacade(
            $this->configFilePoolMock,
            $this->commentParserMock,
            $this->configWriterMock,
            $this->scopeValidatorMock,
            $this->collectorFactoryMock
        );
    }

    /**
     * @expectedExceptionMessage File app/etc/config.php can't be read.
     * @expectedException \Magento\Framework\Exception\RuntimeException
     */
    public function testConfigFileNotExist()
    {
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->with()
            ->willReturnMap([
                [SensitiveConfigSetCommand::INPUT_OPTION_SCOPE, 'default'],
            ]);
        $this->configFilePoolMock->expects($this->once())
            ->method('getPath')
            ->with(ConfigFilePool::APP_CONFIG)
            ->willReturn('config.php');
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->any())
            ->method('execute')
            ->willThrowException(new FileSystemException(new Phrase('some message')));

        $this->command->process(
            $this->inputMock,
            $this->outputMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some exception
     */
    public function testWriterException()
    {
        $exceptionMessage = 'Some exception';
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->with()
            ->willReturnMap([
                [SensitiveConfigSetCommand::INPUT_OPTION_SCOPE, 'default'],
            ]);
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'some/config/path1',
                'some/config/path2'
            ]);
        $collectorMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMockForAbstractClass();
        $collectorMock->expects($this->once())
            ->method('getValues')
            ->willReturn(['some/config/pathNotExist' => 'value']);
        $this->collectorFactoryMock->expects($this->once())
            ->method('create')
            ->with(CollectorFactory::TYPE_SIMPLE)
            ->willReturn($collectorMock);
        $this->configWriterMock->expects($this->once())
            ->method('save')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $this->command->process(
            $this->inputMock,
            $this->outputMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage There are no sensitive configurations to fill
     */
    public function testEmptyConfigPaths()
    {
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->with()
            ->willReturnMap([
                [SensitiveConfigSetCommand::INPUT_OPTION_SCOPE, 'default'],
            ]);
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $this->command->process(
            $this->inputMock,
            $this->outputMock
        );
    }

    public function testExecute()
    {
        $collectedValues = ['some/config/path1' => 'value'];
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->with()
            ->willReturnMap([
                [SensitiveConfigSetCommand::INPUT_OPTION_SCOPE, 'default'],
            ]);
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'some/config/path1',
                'some/config/path2'
            ]);
        $collectorMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMockForAbstractClass();
        $collectorMock->expects($this->once())
            ->method('getValues')
            ->willReturn($collectedValues);
        $this->collectorFactoryMock->expects($this->once())
            ->method('create')
            ->with(CollectorFactory::TYPE_SIMPLE)
            ->willReturn($collectorMock);
        $this->configWriterMock->expects($this->once())
            ->method('save')
            ->with($collectedValues, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, '');

        $this->command->process(
            $this->inputMock,
            $this->outputMock
        );
    }

    public function testExecuteInteractive()
    {
        $collectedValues = ['some/config/path1' => 'value'];
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->with()
            ->willReturnMap([
                [SensitiveConfigSetCommand::INPUT_OPTION_SCOPE, 'default'],
                [SensitiveConfigSetCommand::INPUT_OPTION_INTERACTIVE, true],
            ]);
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('default', '')
            ->willReturn(true);
        $this->commentParserMock->expects($this->once())
            ->method('execute')
            ->willReturn([
                'some/config/path1',
                'some/config/path2',
                'some/config/path3'
            ]);
        $collectorMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMockForAbstractClass();
        $collectorMock->expects($this->once())
            ->method('getValues')
            ->willReturn($collectedValues);
        $this->collectorFactoryMock->expects($this->once())
            ->method('create')
            ->with(CollectorFactory::TYPE_INTERACTIVE)
            ->willReturn($collectorMock);
        $this->configWriterMock->expects($this->once())
            ->method('save')
            ->with($collectedValues, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, '');

        $this->command->process(
            $this->inputMock,
            $this->outputMock
        );
    }
}
