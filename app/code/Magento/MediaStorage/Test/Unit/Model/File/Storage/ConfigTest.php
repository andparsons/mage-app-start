<?php
namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for save method
     */
    public function testSave()
    {
        $config = [];
        $fileStorageMock = $this->createMock(\Magento\MediaStorage\Model\File\Storage::class);
        $fileStorageMock->expects($this->once())->method('getScriptConfig')->will($this->returnValue($config));

        $file = $this->getMockBuilder(\Magento\Framework\Filesystem\File\Write::class)
            ->setMethods(['lock', 'write', 'unlock', 'close'])
            ->disableOriginalConstructor()
            ->getMock();
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with(json_encode($config));
        $file->expects($this->once())->method('unlock');
        $file->expects($this->once())->method('close');
        $directory = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['openFile', 'getRelativePath']
        );
        $directory->expects($this->once())->method('getRelativePath')->will($this->returnArgument(0));
        $directory->expects($this->once())->method('openFile')->with('cacheFile')->will($this->returnValue($file));
        $filesystem = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite']);
        $filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::ROOT
        )->will(
            $this->returnValue($directory)
        );
        $model = new \Magento\MediaStorage\Model\File\Storage\Config($fileStorageMock, $filesystem, 'cacheFile');
        $model->save();
    }
}
