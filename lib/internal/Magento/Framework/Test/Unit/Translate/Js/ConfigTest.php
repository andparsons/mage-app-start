<?php

namespace Magento\Framework\Test\Unit\Translate\Js;

use Magento\Framework\Translate\Js\Config;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testDefault()
    {
        $config = new Config();
        $this->assertFalse($config->dictionaryEnabled());
        $this->assertNull($config->getDictionaryFileName());
    }

    /**
     * @return void
     */
    public function testCustom()
    {
        $path = 'path';
        $config = new Config(true, $path);
        $this->assertTrue($config->dictionaryEnabled());
        $this->assertEquals($path, $config->getDictionaryFileName());
    }
}
