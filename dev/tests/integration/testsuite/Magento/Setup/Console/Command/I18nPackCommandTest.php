<?php
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * @magentoComponentsDir Magento/Setup/Console/Command/_files/root/app/code
 */
class I18nPackCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var I18nCollectPhrasesCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $this->command = new I18nPackCommand();
        $this->tester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        $this->removeCsv('A');
        $this->removeCsv('B');
        $this->removeCsv('C');
        $this->removeCsv('D');
    }

    private function removeCsv($module)
    {
        if (file_exists(__DIR__ . "/_files/root/app/code/Magento/{$module}/i18n")) {
            $helper = new \Magento\Framework\Backup\Filesystem\Helper();
            $helper->rm(__DIR__ . "/_files/root/app/code/Magento/{$module}/i18n", [], true);
        }
    }

    public function testExecute()
    {
        $this->tester->execute(
            [
                'source' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/i18n.csv',
                'locale' => 'de_DE',
                '--allow-duplicates' => true,
            ]
        );

        $this->assertEquals('Successfully saved de_DE language package.' . PHP_EOL, $this->tester->getDisplay());
        $basePath = BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/root/app/code';
        $this->assertFileExists($basePath . '/Magento/A/i18n/de_DE.csv');
        $this->assertFileExists($basePath . '/Magento/B/i18n/de_DE.csv');
        $this->assertFileExists($basePath . '/Magento/C/i18n/de_DE.csv');
        $this->assertFileExists($basePath . '/Magento/D/i18n/de_DE.csv');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot open dictionary file:
     */
    public function testExecuteNonExistingPath()
    {
        $nonExistPath = BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/non_exist.csv';
        $this->tester->execute(
            [
                'source' => $nonExistPath,
                'locale' => 'de_DE',
                '--allow-duplicates' => true,
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Possible values for 'mode' option are 'replace' and 'merge'
     */
    public function testExecuteInvalidMode()
    {
        $this->tester->execute(
            [
                'source' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/i18n.csv',
                'locale' => 'de_DE',
                '--allow-duplicates' => true,
                '--mode' => 'invalid'
            ]
        );
    }
}
