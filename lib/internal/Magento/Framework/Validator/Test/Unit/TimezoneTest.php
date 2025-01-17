<?php

namespace Magento\Framework\Validator\Test\Unit;

class TimezoneTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $expectedTimezones = [
        'Australia/Darwin',
        'America/Los_Angeles',
        'Europe/Kiev',
        'Asia/Jerusalem',
    ];

    public function testIsValid()
    {
        $lists = $this->createMock(\Magento\Framework\Setup\Lists::class);
        $lists->expects($this->any())->method('getTimezoneList')->will($this->returnValue($this->expectedTimezones));
        $timezone = new \Magento\Framework\Validator\Timezone($lists);
        $this->assertEquals(true, $timezone->isValid('America/Los_Angeles'));
    }
}
