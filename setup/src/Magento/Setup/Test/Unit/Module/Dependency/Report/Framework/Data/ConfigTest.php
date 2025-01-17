<?php
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Framework\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleFirst;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleSecond;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Data\Config
     */
    protected $config;

    public function setUp()
    {
        $this->moduleFirst = $this->createMock(\Magento\Setup\Module\Dependency\Report\Framework\Data\Module::class);
        $this->moduleSecond = $this->createMock(\Magento\Setup\Module\Dependency\Report\Framework\Data\Module::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Framework\Data\Config::class,
            ['modules' => [$this->moduleFirst, $this->moduleSecond]]
        );
    }

    public function testGetDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getDependenciesCount')->will($this->returnValue(0));
        $this->moduleSecond->expects($this->once())->method('getDependenciesCount')->will($this->returnValue(2));

        $this->assertEquals(2, $this->config->getDependenciesCount());
    }
}
