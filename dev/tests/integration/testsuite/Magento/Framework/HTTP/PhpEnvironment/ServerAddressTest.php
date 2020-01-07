<?php
namespace Magento\Framework\HTTP\PhpEnvironment;

class ServerAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\ServerAddress
     */
    protected $_helper;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_helper = $objectManager->get(\Magento\Framework\HTTP\PhpEnvironment\ServerAddress::class);
    }

    public function testGetServerAddress()
    {
        $this->assertEquals(false, $this->_helper->getServerAddress());
    }
}
