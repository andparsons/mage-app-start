<?php
namespace Magento\SomeModule\Model\Six;

require_once __DIR__ . '/../One/Test.php';
require_once __DIR__ . '/../Proxy.php';
require_once __DIR__ . '/../ElementFactory.php';
class Test extends \Magento\SomeModule\Model\One\Test
{
    /**
     * @var \Magento\SomeModule\Model\ElementFactory
     */
    protected $_factory;

    /**
     * Test constructor.
     * @param \Magento\SomeModule\Model\Proxy $proxy
     * @param \Magento\SomeModule\Model\ElementFactory $factory
     */
    public function __construct(
        \Magento\SomeModule\Model\Proxy $proxy,
        \Magento\SomeModule\Model\ElementFactory $factory
    ) {
        $this->_factory = $factory;
        parent::__construct($factory);
    }
}
