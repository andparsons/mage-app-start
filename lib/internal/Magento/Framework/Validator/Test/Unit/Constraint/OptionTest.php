<?php

namespace Magento\Framework\Validator\Test\Unit\Constraint;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Option
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test getValue
     */
    public function testGetValue()
    {
        $expected = 'test_value';
        $option = new \Magento\Framework\Validator\Constraint\Option($expected);
        $this->assertEquals($expected, $option->getValue());
    }
}
