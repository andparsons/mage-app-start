<?php
namespace Magento\Framework\Filter\Test\Unit\Template\Tokenizer;

use \Magento\Framework\Filter\Template\Tokenizer\Variable;

class VariableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filter\Template\Tokenizer\Variable
     */
    protected $_filter;

    protected function setUp()
    {
        $this->_filter = new Variable();
    }

    /**
     * @param string $string String to tokenize
     * @param string $expectedValue
     * @dataProvider sampleTokenizeStringProvider
     */
    public function testTokenize($string, $expectedValue)
    {
        $this->_filter->setString($string);
        $this->assertEquals($expectedValue, $this->_filter->tokenize());
    }

    /**
     * @return array
     */
    public function sampleTokenizeStringProvider()
    {
        return [
            ["firstname", [['type' => 'variable', 'name' => 'firstname']]],
            [
                "invoke(arg1, arg2, 2, 2.7, -1, 'Mike\\'s')",
                [['type' => 'method', 'name' => 'invoke', 'args' => ['arg1', 'arg2', 2, 2.7, -1, "Mike's"]]]
            ],
            [
                'var.method("value_1", [ _param_1:$bogus.prop,
                    _param_2:$foo.bar,_param_3:12345,
                    call:$var.method("param"),
                    id:foobar,
                    [123, foobar],
                    bar:["foo", 1234, $foo.bar],
                    "foo:bar":[bar, "1234", \'$foo.bar\'],
                ])',
                [
                    ['type' => 'variable', 'name' => 'var'],
                    ['type' => 'method', 'name' => 'method', 'args' => [
                        'value_1',
                        [
                            '_param_1' => '$bogus.prop',
                            '_param_2' => '$foo.bar',
                            '_param_3' => 12345,
                            'call' => '$var.method("param")',
                            'id' => 'foobar',
                            0 => [123, 'foobar'],
                            'bar' => ['foo', 1234, '$foo.bar'],
                            'foo:bar' => ['bar', "1234", '$foo.bar'],
                        ],
                    ]],
                ],
            ],
            ["  ", []],
        ];
    }
}
