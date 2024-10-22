<?php
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ObjectRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry */
    protected $objectRegistry;

    /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    protected function setUp()
    {
        $this->object = new \Magento\Framework\DataObject(['id' => 1]);
        $this->objectRegistry = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\ObjectRegistry::class,
            ['entities' => [$this->object]]
        );
    }

    public function testGet()
    {
        $this->assertEquals($this->object, $this->objectRegistry->get(1));
    }

    public function testGetNotExistObject()
    {
        $this->assertEquals(null, $this->objectRegistry->get('no-id'));
    }

    public function testGetList()
    {
        $this->assertEquals([1 => $this->object], $this->objectRegistry->getList());
    }

    public function testGetEmptyList()
    {
        $objectRegistry = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\ObjectRegistry::class,
            ['entities' => []]
        );
        $this->assertEquals([], $objectRegistry->getList());
    }
}
