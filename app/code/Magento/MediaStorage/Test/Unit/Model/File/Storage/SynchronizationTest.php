<?php
namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SynchronizationTest extends \PHPUnit\Framework\TestCase
{
    public function testSynchronize()
    {
        $content = 'content';
        $relativeFileName = 'config.xml';

        $storageFactoryMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\DatabaseFactory::class,
            ['create', '_wakeup']
        );
        $storageMock = $this->createPartialMock(
            \Magento\MediaStorage\Model\File\Storage\Database::class,
            ['getContent', 'getId', 'loadByFilename', '__wakeup']
        );
        $storageFactoryMock->expects($this->once())->method('create')->will($this->returnValue($storageMock));

        $storageMock->expects($this->once())->method('getContent')->will($this->returnValue($content));
        $storageMock->expects($this->once())->method('getId')->will($this->returnValue(true));
        $storageMock->expects($this->once())->method('loadByFilename');

        $file = $this->createPartialMock(
            \Magento\Framework\Filesystem\File\Write::class,
            ['lock', 'write', 'unlock', 'close']
        );
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with($content);
        $file->expects($this->once())->method('unlock');
        $file->expects($this->once())->method('close');
        $directory = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $directory->expects($this->once())
            ->method('openFile')
            ->with($relativeFileName)
            ->will($this->returnValue($file));

        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(\Magento\MediaStorage\Model\File\Storage\Synchronization::class, [
            'storageFactory' => $storageFactoryMock,
            'directory' => $directory,
        ]);
        $model->synchronize($relativeFileName);
    }
}
