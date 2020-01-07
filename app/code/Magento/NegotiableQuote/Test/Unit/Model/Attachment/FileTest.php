<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Attachment;

/**
 * Class FileTest
 */
class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileDriver;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->fileDriver = $this->createMock(
            \Magento\Framework\Filesystem\Driver\File::class
        );
        $this->fileFactory = $this->createPartialMock(
            \Magento\Framework\App\Response\Http\FileFactory::class,
            ['create']
        );
        $this->filesystem = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryRead']);
        $directory = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($directory);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->file = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Attachment\File::class,
            [
                'fileDriver' => $this->fileDriver,
                'fileFactory' => $this->fileFactory,
                'filesystem' => $this->filesystem,

            ]
        );
    }

    /**
     * Test getContents()
     */
    public function testGetContents()
    {
        $comment = $this->getMockForAbstractClass(\Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface::class);
        $this->file->downloadContents($comment);
    }

    /**
     * Test getContents() with Exception
     *
     * @expectedException \Exception
     */
    public function testGetContentsWithException()
    {
        $comment = $this->getMockForAbstractClass(\Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface::class);
        $exceptionMessage = 'An error occurred.';
        $exception = new \Exception(__($exceptionMessage));
        $this->fileFactory->expects($this->any())->method('create')->willThrowException($exception);
        $this->file->downloadContents($comment);
    }

    /**
     * Test getContents() with Exception
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetContentsWithInvalidArgumentException()
    {
        $comment = $this->getMockForAbstractClass(\Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface::class);
        $exceptionMessage = 'Invalid arguments. Keys \'type\' and \'value\' are required.';
        $exception = new \InvalidArgumentException(__($exceptionMessage));
        $this->fileFactory->expects($this->any())->method('create')->willThrowException($exception);
        $this->file->downloadContents($comment);
    }
}
