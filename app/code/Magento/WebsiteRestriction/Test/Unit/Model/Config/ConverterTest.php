<?php
namespace Magento\WebsiteRestriction\Test\Unit\Model\Config;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\WebsiteRestriction\Model\Config\Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    protected function setUp()
    {
        $this->_model = new \Magento\WebsiteRestriction\Model\Config\Converter();
        $this->_filePath = realpath(__DIR__) . '/_files/';
    }

    public function testConvert()
    {
        $dom = new \DOMDocument();
        $dom->load($this->_filePath . 'webrestrictions.xml');
        $actual = $this->_model->convert($dom);
        $expected = require $this->_filePath . 'webrestrictions.php';
        $this->assertEquals($expected, $actual);
    }
}
