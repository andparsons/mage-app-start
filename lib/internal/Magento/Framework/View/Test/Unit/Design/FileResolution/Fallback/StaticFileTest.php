<?php

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback;

use \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile;

use Magento\Framework\View\Design\Fallback\RulePool;

class StaticFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolver;

    /**
     * @var StaticFile
     */
    protected $object;

    protected function setUp()
    {
        $this->resolver = $this->createMock(
            \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface::class
        );
        $this->object = new StaticFile($this->resolver);
    }

    public function testGetFile()
    {
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_STATIC_FILE, 'file.ext', 'frontend', $theme, 'en_US', 'Magento_Module')
            ->will($this->returnValue($expected));
        $actual = $this->object->getFile('frontend', $theme, 'en_US', 'file.ext', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }
}
