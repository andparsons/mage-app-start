<?php
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

class I18nCollectPhrasesCommandTest extends \PHPUnit\Framework\TestCase
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
        $this->command = new I18nCollectPhrasesCommand();
        $this->tester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        $property = new \ReflectionProperty(\Magento\Setup\Module\I18n\ServiceLocator::class, '_dictionaryGenerator');
        $property->setAccessible(true);
        $property->setValue(null);
        $property->setAccessible(false);
    }

    public function testExecuteConsoleOutput()
    {
        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/',
            ]
        );

        $this->assertEquals('Dictionary successfully processed.' . PHP_EOL, $this->tester->getDisplay());
    }

    public function testExecuteCsvOutput()
    {
        $outputPath = BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/phrases.csv';
        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/phrases/',
                '--output' => $outputPath,
            ]
        );

        $handle = fopen($outputPath, 'r');
        $output = fread($handle, filesize($outputPath));
        $expected = file_get_contents(
            BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/expectedPhrases.csv'
        );
        $this->assertEquals($expected, $output);
        unlink($outputPath);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specified path doesn't exist
     */
    public function testExecuteNonExistingPath()
    {
        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/non_exist',
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Directory path is not needed when --magento flag is set.
     */
    public function testExecuteMagentoFlagDirectoryPath()
    {
        $this->tester->execute(['directory' => 'a', '--magento' => true]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Directory path is needed when --magento flag is not set.
     */
    public function testExecuteNoMagentoFlagNoDirectoryPath()
    {
        $this->tester->execute([]);
    }
}
