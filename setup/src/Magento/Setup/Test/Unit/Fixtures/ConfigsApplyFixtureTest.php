<?php

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Setup\Fixtures\ConfigsApplyFixture;

class ConfigsApplyFixtureTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\ConfigsApplyFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->createMock(\Magento\Setup\Fixtures\FixtureModel::class);

        $this->model = new ConfigsApplyFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $cacheMock = $this->createMock(\Magento\Framework\App\Cache::class);

        $valueMock = $this->createMock(\Magento\Framework\App\Config::class);
        $configMock = $this->createMock(\Magento\Config\App\Config\Type\System::class);

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock
            ->method('get')
            ->willReturnMap([
                [\Magento\Framework\App\CacheInterface::class, $cacheMock],
                [\Magento\Config\App\Config\Type\System::class, $configMock]
            ]);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(['config' => $valueMock]));
        $this->fixtureModelMock
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $cacheMock->method('clean');
        $configMock->method('clean');

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $configMock = $this->getMockBuilder(\Magento\Framework\App\Config\ValueInterface::class)
            ->setMethods(['save'])
            ->getMockForAbstractClass();
        $configMock->expects($this->never())->method('save');

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->willReturn($configMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Config Changes', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([], $this->model->introduceParamLabels());
    }
}
