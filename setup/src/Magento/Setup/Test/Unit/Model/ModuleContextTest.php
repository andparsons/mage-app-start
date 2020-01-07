<?php
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ModuleContext;

class ModuleContextTest extends \PHPUnit\Framework\TestCase
{
    public function testGetVersion()
    {
        $version = '1.0.1';
        $object = new ModuleContext($version);
        $this->assertSame($version, $object->getVersion());
    }
}
