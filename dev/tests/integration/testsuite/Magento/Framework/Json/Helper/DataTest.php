<?php
namespace Magento\Framework\Json\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helper = null;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Json\Helper\Data::class
        );
    }

    public function testJsonEncodeDecode()
    {
        $data = ['one' => 1, 'two' => 'two'];
        $jsonData = '{"one":1,"two":"two"}';
        $this->assertEquals($jsonData, $this->_helper->jsonEncode($data));
        $this->assertEquals($data, $this->_helper->jsonDecode($jsonData));
    }
}
