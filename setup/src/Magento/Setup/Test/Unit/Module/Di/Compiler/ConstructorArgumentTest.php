<?php
namespace Magento\Setup\Test\Unit\Module\Di\Compiler;

class ConstructorArgumentTest extends \PHPUnit\Framework\TestCase
{
    public function testInterface()
    {
        $argument = ['configuration', 'array', true, null];
        $model = new \Magento\Setup\Module\Di\Compiler\ConstructorArgument($argument);
        $this->assertEquals($argument[0], $model->getName());
        $this->assertEquals($argument[1], $model->getType());
        $this->assertTrue($model->isRequired());
        $this->assertNull($model->getDefaultValue());
    }
}
