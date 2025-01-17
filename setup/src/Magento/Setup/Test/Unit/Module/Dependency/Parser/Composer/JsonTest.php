<?php
namespace Magento\Setup\Test\Unit\Module\Dependency\Parser\Composer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class JsonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Parser\Config\Xml
     */
    protected $parser;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->parser = $objectManagerHelper->getObject(\Magento\Setup\Module\Dependency\Parser\Composer\Json::class);
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parse error: Option "files_for_parse" is wrong.
     * @dataProvider dataProviderWrongOptionFilesForParse
     */
    public function testParseWithWrongOptionFilesForParse($options)
    {
        $this->parser->parse($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionFilesForParse()
    {
        return [
            [['files_for_parse' => []]],
            [['files_for_parse' => 'string']],
            [['there_are_no_files_for_parse' => [1, 3]]]
        ];
    }
}
