<?php
namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use \Magento\Framework\Data\Argument\Interpreter\NullType;

class NullTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testEvaluate()
    {
        $object = new NullType();
        $this->assertNull($object->evaluate(['unused']));
    }
}
