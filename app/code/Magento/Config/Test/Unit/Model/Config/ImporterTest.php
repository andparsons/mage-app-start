<?php
namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\Importer;
use Magento\Config\Model\Config\Importer\SaveProcessor;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Flag;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\ArrayUtils;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for Importer.
 *
 * @see Importer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImporterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Importer
     */
    private $model;

    /**
     * @var FlagManager|Mock
     */
    private $flagManagerMock;

    /**
     * @var Flag|Mock
     */
    private $flagMock;

    /**
     * @var ArrayUtils|Mock
     */
    private $arrayUtilsMock;

    /**
     * @var PreparedValueFactory|Mock
     */
    private $valueFactoryMock;

    /**
     * @var ScopeConfigInterface|Mock
     */
    private $scopeConfigMock;

    /**
     * @var State|Mock
     */
    private $stateMock;

    /**
     * @var ScopeInterface|Mock
     */
    private $scopeMock;

    /**
     * @var Value|Mock
     */
    private $valueMock;

    /**
     * @var SaveProcessor|Mock
     */
    private $saveProcessorMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->setMethods(['create', 'getFlagData', 'saveFlag'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayUtilsMock = $this->getMockBuilder(ArrayUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueFactoryMock = $this->getMockBuilder(PreparedValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->saveProcessorMock = $this->getMockBuilder(SaveProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->flagManagerMock->expects($this->any())
            ->method('create')
            ->willReturn($this->flagMock);

        $this->model = new Importer(
            $this->flagManagerMock,
            $this->arrayUtilsMock,
            $this->saveProcessorMock,
            $this->scopeConfigMock,
            $this->stateMock,
            $this->scopeMock
        );
    }

    public function testImport()
    {
        $data = [];
        $currentData = ['current' => '2'];

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(Importer::FLAG_CODE)
            ->willReturn($currentData);
        $this->arrayUtilsMock->expects($this->exactly(2))
            ->method('recursiveDiff')
            ->willReturnMap([
                [$data, $currentData, []],
                [$currentData, $data, []]
            ]);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('oldScope');
        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->with(Area::AREA_ADMINHTML, $this->anything())
            ->willReturnCallback(function ($area, $function) {
                $this->assertEquals(Area::AREA_ADMINHTML, $area);
                return $function();
            });
        $this->saveProcessorMock->expects($this->once())
            ->method('process')
            ->with([]);
        $this->scopeMock->expects($this->at(1))
            ->method('setCurrentScope')
            ->with(Area::AREA_ADMINHTML);
        $this->scopeMock->expects($this->at(2))
            ->method('setCurrentScope')
            ->with('oldScope');
        $this->scopeMock->expects($this->at(3))
            ->method('setCurrentScope')
            ->with('oldScope');
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with(Importer::FLAG_CODE, $data);

        $this->assertSame(['System config was processed'], $this->model->import($data));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Some error
     */
    public function testImportWithException()
    {
        $data = [];
        $currentData = ['current' => '2'];

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->willReturn($currentData);
        $this->arrayUtilsMock->expects($this->exactly(2))
            ->method('recursiveDiff')
            ->willReturnMap([
                [$data, $currentData, []],
                [$currentData, $data, []]
            ]);
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('oldScope');
        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->willThrowException(new \Exception('Some error'));
        $this->scopeMock->expects($this->once())
            ->method('setCurrentScope')
            ->with('oldScope');

        $this->model->import($data);
    }
}
