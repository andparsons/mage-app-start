<?php
namespace Magento\Framework\HTTP\PhpEnvironment;

class RemoteAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_helper;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_helper = $objectManager->get(\Magento\Framework\HTTP\PhpEnvironment\RemoteAddress::class);
    }

    public function testGetRemoteAddress()
    {
        $this->assertEquals(false, $this->_helper->getRemoteAddress());
    }
}
