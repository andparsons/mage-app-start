<?php
namespace Magento\Ui\Test\Unit\Config\Converter;

use Magento\Ui\Config\Converter\Url;
use Magento\Ui\Config\ConverterUtils;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Url
     */
    private $converter;

    public function setUp()
    {
        $this->converter = new Url(new ConverterUtils());
    }

    public function testConvertUrl()
    {
        $expectedResult = [
            'name' => 'url',
            'xsi:type' => 'url',
            'path' => 'some_url',
            'param' => [
                'first' => [
                    'name' => 'first',
                    'value' => 'first_value',
                ],
                'second'=> [
                    'name' => 'second',
                    'value' => 'second_value',
                ],
            ],
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test.xml');
        $domXpath = new \DOMXPath($dom);
        $url = $domXpath->query('//listing/settings/buttons/button[@name="button_2"]/url')->item(0);
        $this->assertEquals($expectedResult, $this->converter->convert($url));
    }

    public function testConvertUrlWithoutParams()
    {
        $expectedResult = [
            'name' => 'path',
            'xsi:type' => 'url',
            'path' => 'path',
        ];
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test.xml');
        $domXpath = new \DOMXPath($dom);
        $url = $domXpath->query('//listing/settings/storageConfig/path')->item(0);
        $this->assertEquals($expectedResult, $this->converter->convert($url));
    }
}
