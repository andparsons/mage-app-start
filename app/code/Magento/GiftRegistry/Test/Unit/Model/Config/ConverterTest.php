<?php

/**
 * Check Config Reader and Converter to receive the right array of data
 *
 * Class \Magento\GiftRegistry\Model\Config\ConverterTest
 */
namespace Magento\GiftRegistry\Test\Unit\Model\Config;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GiftRegistry\Model\Config\Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var \DOMDocument
     */
    protected $_source;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->_filePath = __DIR__ . '/../_files/';
        $this->_source = new \DOMDocument();
        $this->_model = new \Magento\GiftRegistry\Model\Config\Converter();
    }

    /**
     * Test Xml structure without translations
     */
    public function testConvert()
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . 'config_valid.xml'));
        $convertedFile = include $this->_filePath . 'giftregistry_config.php';
        $converted = $this->_model->convert($this->_source);

        $this->assertEquals($converted, $convertedFile);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @param string $invalidConfFileName
     * @dataProvider invalidConfigFilesDataProvider
     */
    public function testConvertThrowsExceptionWhenDomIsInvalid($invalidConfFileName)
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . $invalidConfFileName));
        $this->_model->convert($this->_source);
    }

    /**
     * Data provider for testConvertThrowsExceptionWhenDomIsInvalid
     *
     * @return array
     */
    public function invalidConfigFilesDataProvider()
    {
        return [
            ['config_absent_attribute_name_attrname.xml'],
            ['config_absent_attribute_group_attrname.xml'],
            ['config_absent_static_attribute_attrname.xml'],
            ['config_absent_custom_attribute_attrname.xml']
        ];
    }
}
