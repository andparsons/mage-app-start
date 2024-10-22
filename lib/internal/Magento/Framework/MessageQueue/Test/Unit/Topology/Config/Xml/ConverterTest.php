<?php
namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\Xml;

use Magento\Framework\MessageQueue\Topology\Config\Xml\Converter;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Data\Argument\InterpreterInterface;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $interpreter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultConfigProviderMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->defaultConfigProviderMock =
            $this->createMock(\Magento\Framework\MessageQueue\DefaultValueProvider::class);
        $this->interpreter = $this->createMock(InterpreterInterface::class);
        $this->converter = new Converter(new BooleanUtils(), $this->interpreter, $this->defaultConfigProviderMock);
        $this->defaultConfigProviderMock->expects($this->any())->method('getConnection')->willReturn('amqp');
    }

    public function testConvert()
    {
        $fixtureDir = __DIR__ . '/../../../_files/queue_topology';
        $xmlFile = $fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);

        $this->interpreter->expects($this->any())->method('evaluate')->willReturn(10);
        $result = $this->converter->convert($dom);

        $expectedData = include($fixtureDir . '/valid.php');
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $result[$key], 'Invalid data for ' . $key);
        }
    }
}
