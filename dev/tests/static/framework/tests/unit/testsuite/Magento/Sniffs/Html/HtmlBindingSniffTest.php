<?php

declare(strict_types=1);

namespace Magento\Sniffs\Html;

use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\HtmlWrapper;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;

/**
 * Test the html binding sniff on real files.
 */
class HtmlBindingSniffTest extends TestCase
{
    /**
     * Files to sniff and expected reports.
     *
     * @return array
     */
    public function processDataProvider(): array
    {
        return [
            [
                'test-html-binding.html',
                'test-html-binding-errors.txt'
            ]
        ];
    }

    /**
     * Run CS on provided files.
     *
     * @param string $fileUnderTest
     * @param string $expectedReportFile
     * @return void
     * @dataProvider processDataProvider
     */
    public function testProcess(string $fileUnderTest, string $expectedReportFile): void
    {
        $reportFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'phpcs_report.txt';
        $ruleSetDir = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        $wrapper = new HtmlWrapper();
        $codeSniffer = new CodeSniffer($ruleSetDir, $reportFile, $wrapper);
        $codeSniffer->setExtensions([HtmlWrapper::FILE_EXTENSION]);
        $result = $codeSniffer->run(
            [__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $fileUnderTest]
        );
        // Remove the absolute path to the file from the output
        //phpcs:ignore
        $actual = preg_replace('/^.+\n/', '', ltrim(file_get_contents($reportFile)));
        //phpcs:ignore
        $expected = file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $expectedReportFile
        );
        //phpcs:ignore
        unlink($reportFile);
        $this->assertEquals(1, $result);
        $this->assertEquals($expected, $actual);
    }
}
