<?php
namespace Magento\Integration\Test\Unit\Model\Config\Integration;

use \Magento\Integration\Model\Config\Integration\Converter;

/**
 * Test for conversion of integration API XML config into array representation.
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/api.xml');
        $expectedResult = require __DIR__ . '/_files/api.php';
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
