<?php
namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\ComponentFile;

class ComponentFileTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters()
    {
        $type = 'type';
        $name = 'name';
        $path = 'path';
        $component = new ComponentFile($type, $name, $path);
        $this->assertSame($type, $component->getComponentType());
        $this->assertSame($name, $component->getComponentName());
        $this->assertSame($path, $component->getFullPath());
    }
}
