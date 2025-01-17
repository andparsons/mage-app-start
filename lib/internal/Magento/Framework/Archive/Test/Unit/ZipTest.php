<?php

namespace Magento\Framework\Archive\Test\Unit;

use Composer\Composer;

class ZipTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\Archive\Zip|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $zip;

    protected function setUp()
    {
        $this->zip = $this->getMockBuilder(\Magento\Framework\Archive\Zip::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Check constructor if no exceptions is thrown.
     */
    public function testConstructorNoExceptions()
    {
        try {
            $reflectedClass = new \ReflectionClass(\Magento\Framework\Archive\Zip::class);
            $constructor = $reflectedClass->getConstructor();
            $constructor->invoke($this->zip, []);
        } catch (\Exception $e) {
            $this->fail('Failed asserting that no exceptions is thrown');
        }
    }

    /**
     * @depends testConstructorNoExceptions
     */
    public function testPack()
    {
        $this->markTestSkipped('Method pack contains dependency on \ZipArchive object');
    }

    /**
     * @depends testConstructorNoExceptions
     */
    public function testUnpack()
    {
        $this->markTestSkipped('Method unpack contains dependency on \ZipArchive object');
    }
}
