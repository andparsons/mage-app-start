<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Attachment;

/**
 * Class DownloadProviderTest
 */
class DownloadProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\CommentAttachmentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentAttachmentFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowInterface
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $allowDownload;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\DownloadProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $downloadProvider;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attachment;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->attachment = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\CommentAttachment::class,
            ['load', 'getAttachmentId']
        );
        $this->attachment->expects($this->any())->method('load')->willReturnSelf();
        $this->commentAttachmentFactory =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\CommentAttachmentFactory::class, ['create']);
        $this->commentAttachmentFactory->expects($this->any())->method('create')->willReturn($this->attachment);
        $this->fileFactory =
            $this->createPartialMock(\Magento\Framework\App\Response\Http\FileFactory::class, ['create']);
        $this->allowDownload = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowInterface::class,
            ['isAllowed']
        );

        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->file = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\Attachment\File::class,
            ['downloadContents']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->downloadProvider = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Attachment\DownloadProvider::class,
            [
                'commentAttachmentFactory' => $this->commentAttachmentFactory,
                'fileFactory' => $this->fileFactory,
                'allowDownload' => $this->allowDownload,
                'logger' => $this->logger,
                'file' => $this->file,
                'attachmentId' => 1

            ]
        );
    }

    /**
     * Test getAttachmentContents()
     */
    public function testGetAttachmentContents()
    {
        $this->allowDownload->expects($this->any())->method('isAllowed')->willReturn(true);
        $this->attachment->expects($this->any())->method('getAttachmentId')->willReturn(1);
        $this->file->expects($this->once())->method('downloadContents')->willReturn('contents');
        $this->downloadProvider->getAttachmentContents();
    }

    /**
     * Test getAttachmentContents() with exception
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetAttachmentContentsWithException()
    {
        $this->allowDownload->expects($this->any())->method('isAllowed')->willReturn(true);
        $this->attachment->expects($this->any())->method('getAttachmentId')->willReturn(null);
        $this->downloadProvider->getAttachmentContents();
    }
}
