<?php
namespace Magento\Framework\Url\Test\Unit;

class SecurityInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\Framework\Url\SecurityInfo
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Url\SecurityInfo(['/account', '/cart'], ['/cart/remove', 'customer']);
    }

    /**
     * @param string $url
     * @param bool $expected
     * @dataProvider secureUrlDataProvider
     */
    public function testIsSecureChecksIfUrlIsInSecureList($url, $expected)
    {
        $this->assertEquals($expected, $this->_model->isSecure($url));
    }

    /**
     * @return array
     */
    public function secureUrlDataProvider()
    {
        return [
            ['/account', true],
            ['/product', false],
            ['/product/12312', false],
            ['/cart', true],
            ['/cart/add', true],
            ['/cart/remove', false],
            ['/customer', false]
        ];
    }
}
