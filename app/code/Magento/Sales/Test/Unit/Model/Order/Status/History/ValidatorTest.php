<?php

namespace Magento\Sales\Test\Unit\Model\Order\Status\History;

use \Magento\Sales\Model\Order\Status\History\Validator;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testValidate()
    {
        $history = $this->createPartialMock(\Magento\Sales\Model\Order\Status\History::class, ['hasData']);
        $history->expects($this->any())
            ->method('hasData')
            ->will($this->returnValue(true));
        $validator = new Validator();
        $this->assertEmpty($validator->validate($history));
    }

    public function testValidateNegative()
    {
        $history = $this->createPartialMock(\Magento\Sales\Model\Order\Status\History::class, ['hasData']);
        $history->expects($this->any())
            ->method('hasData')
            ->with('parent_id')
            ->will($this->returnValue(false));
        $validator = new Validator();
        $this->assertEquals(['"Order Id" is required. Enter and try again.'], $validator->validate($history));
    }
}
