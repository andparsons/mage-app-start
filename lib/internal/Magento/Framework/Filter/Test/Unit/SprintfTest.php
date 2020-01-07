<?php

namespace Magento\Framework\Filter\Test\Unit;

class SprintfTest extends \PHPUnit\Framework\TestCase
{
    public function testFilter()
    {
        $sprintfFilter = new \Magento\Framework\Filter\Sprintf('Formatted value: "%s"', 2, ',', ' ');
        $this->assertEquals('Formatted value: "1 234,57"', $sprintfFilter->filter(1234.56789));
    }
}
