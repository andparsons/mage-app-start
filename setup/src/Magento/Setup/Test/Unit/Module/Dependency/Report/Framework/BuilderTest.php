<?php
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Framework;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Builder
     */
    protected $builder;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->builder = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Framework\Builder::class
        );
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parse error. Passed option "config_files" is wrong.
     * @dataProvider dataProviderWrongOptionConfigFiles
     */
    public function testBuildWithWrongOptionConfigFiles($options)
    {
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionConfigFiles()
    {
        return [
            [
                [
                    'parse' => ['files_for_parse' => [1, 2], 'config_files' => []],
                    'write' => [1, 2],
                ],
            ],
            [['parse' => ['files_for_parse' => [1, 2]], 'write' => [1, 2]]]
        ];
    }
}
