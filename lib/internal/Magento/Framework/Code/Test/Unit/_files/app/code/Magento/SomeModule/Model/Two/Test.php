<?php
namespace Magento\SomeModule\Model\Two;

require_once __DIR__ . '/../One/Test.php';
require_once __DIR__ . '/../Proxy.php';
class Test extends \Magento\SomeModule\Model\One\Test
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    /**
     * Test constructor.
     * @param \Magento\SomeModule\Model\Proxy $proxy
     * @param array $data
     */
    public function __construct(\Magento\SomeModule\Model\Proxy $proxy, $data = [])
    {
        $this->_proxy = $proxy;
    }
}
