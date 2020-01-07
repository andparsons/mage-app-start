<?php
namespace Magento\TestFramework\CodingStandard\Tool;

class CodeMessDetectorTest extends \PHPUnit\Framework\TestCase
{
    public function testCanRun()
    {
        $messDetector = new \Magento\TestFramework\CodingStandard\Tool\CodeMessDetector(
            'some/ruleset/file.xml',
            'some/report/file.xml'
        );

        $this->assertEquals(
            class_exists(\PHPMD\TextUI\Command::class),
            $messDetector->canRun()
        );
    }
}
