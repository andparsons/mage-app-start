<?php
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use \Magento\Framework\View\Layout\Argument\Interpreter\Passthrough;

class PassthroughTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Passthrough
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Passthrough();
    }

    public function testEvaluate()
    {
        $input = ['data' => 'some/value'];
        $expected = ['data' => 'some/value'];

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }
}
