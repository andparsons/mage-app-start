<?php

namespace Magento\Framework\App\Test\Unit\View\Deployment\Version\Storage;

use \Magento\Framework\App\View\Deployment\Version\Storage\File;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var File
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $directory;

    protected function setUp()
    {
        $this->directory = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with('fixture_dir')
            ->will($this->returnValue($this->directory));
        $this->object = new File($filesystem, 'fixture_dir', 'fixture_file.txt');
    }

    public function testLoad()
    {
        $this->directory->expects($this->once())
            ->method('isReadable')
            ->with('fixture_file.txt')
            ->willReturn(true);
        $this->directory->expects($this->once())
            ->method('readFile')
            ->with('fixture_file.txt')
            ->willReturn('123');
        $this->assertEquals('123', $this->object->load());
    }

    public function testSave()
    {
        $this->directory
            ->expects($this->once())
            ->method('writeFile')
            ->with('fixture_file.txt', 'input_data', 'w');
        $this->object->save('input_data');
    }
}
