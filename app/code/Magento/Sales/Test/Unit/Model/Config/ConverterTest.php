<?php
namespace Magento\Sales\Test\Unit\Model\Config;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Config\Converter
     */
    protected $_converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->_converter = new \Magento\Sales\Model\Config\Converter();
    }

    /**
     * Testing wrong data incoming
     */
    public function testConvertWrongIncomingData()
    {
        $result = $this->_converter->convert(['wrong data']);
        $this->assertEmpty($result);
    }

    /**
     * Testing empty data
     */
    public function testConvertNoElements()
    {
        $result = $this->_converter->convert(new \DOMDocument());
        $this->assertEmpty($result);
    }

    /**
     * Testing converting valid cron configuration
     */
    public function testConvert()
    {
        $expected = [
            'section1' => [
                'group1' => [
                    'item1' => [
                        'instance' => 'instance1',
                        'sort_order' => '1',
                        'renderers' => ['renderer1' => 'instance1'],
                    ],
                ],
                'group2' => [
                    'item1' => ['instance' => 'instance1', 'sort_order' => '1', 'renderers' => []],
                ],
            ],
            'section2' => [
                'group1' => [
                    'item1' => ['instance' => 'instance1', 'sort_order' => '1', 'renderers' => []],
                ],
            ],
            'order' => ['available_product_types' => ['type1', 'type2']],
        ];

        $xmlFile = __DIR__ . '/_files/sales_valid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->_converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Testing converting not valid cron configuration, expect to get exception
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertWrongConfiguration()
    {
        $xmlFile = __DIR__ . '/_files/sales_invalid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $this->_converter->convert($dom);
    }
}
