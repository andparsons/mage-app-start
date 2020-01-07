<?php
namespace Magento\CacheInvalidate\Test\Unit\Model;

class SocketFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $factory = new \Magento\CacheInvalidate\Model\SocketFactory();
        $this->assertInstanceOf(\Zend\Http\Client\Adapter\Socket::class, $factory->create());
    }
}
