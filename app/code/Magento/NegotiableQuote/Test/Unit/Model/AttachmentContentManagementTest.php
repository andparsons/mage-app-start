<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\CollectionFactory;

/**
 * Unit test for AttachmentContentManagement model.
 */
class AttachmentContentManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attachmentCollectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attachmentContentFactory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileDriver;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\AttachmentContentManagement
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->attachmentCollectionFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->attachmentContentFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->fileDriver = $this->getMockBuilder(\Magento\Framework\Filesystem\Driver\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readInterface = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\AttachmentContentManagement::class,
            [
                'attachmentCollectionFactory' => $this->attachmentCollectionFactory,
                'attachmentContentFactory' => $this->attachmentContentFactory,
                'fileDriver' => $this->fileDriver,
                'mediaDirectory' => $this->readInterface,
            ]
        );
    }

    /**
     * Test get method.
     *
     * @return void
     */
    public function testGet()
    {
        $attachmentIds = [1];
        $attachmentCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $attachment = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attachmentContent = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attachmentCollectionFactory->expects($this->once())->method('create')->willReturn($attachmentCollection);
        $attachmentCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('attachment_id', ['in' => $attachmentIds])
            ->willReturnSelf();
        $attachmentCollection->expects($this->once())->method('getItems')->willReturn([$attachment]);
        $this->readInterface->expects($this->once())
            ->method('getAbsolutePath')
            ->with(\Magento\NegotiableQuote\Model\CommentManagement::ATTACHMENTS_FOLDER)
            ->willReturn('pub/media/negotiable_quotes_attachment/');
        $attachment->expects($this->once())->method('getFilePath')->willReturn('2/3/test.txt');
        $this->fileDriver->expects($this->once())
            ->method('fileGetContents')
            ->with('pub/media/negotiable_quotes_attachment/2/3/test.txt')
            ->willReturn('file content');
        $this->attachmentContentFactory->expects($this->once())
            ->method('create')
            ->willReturn($attachmentContent);
        $attachment->expects($this->once())->method('getFileType')->willReturn('text/plain');
        $attachment->expects($this->once())->method('getFileName')->willReturn('test.txt');
        $attachmentCollection->expects($this->once())->method('getAllIds')->willReturn([1]);

        $this->assertSame([$attachmentContent], $this->model->get($attachmentIds));
    }

    /**
     * Test get method if some attachments don't exist.
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Cannot obtain the requested data. You must fix the errors listed below first.
     * @return void
     */
    public function testGetWithException()
    {
        $attachmentIds = [1, 2, 3];
        $attachmentCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $attachment = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attachmentContent = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attachmentCollectionFactory->expects($this->once())->method('create')->willReturn($attachmentCollection);
        $attachmentCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('attachment_id', ['in' => $attachmentIds])
            ->willReturnSelf();
        $attachmentCollection->expects($this->once())->method('getItems')->willReturn([$attachment]);
        $this->readInterface->expects($this->once())
            ->method('getAbsolutePath')
            ->with(\Magento\NegotiableQuote\Model\CommentManagement::ATTACHMENTS_FOLDER)
            ->willReturn('pub/media/negotiable_quotes_attachment/');
        $attachment->expects($this->once())->method('getFilePath')->willReturn('2/3/test.txt');
        $this->fileDriver->expects($this->once())
            ->method('fileGetContents')
            ->with('pub/media/negotiable_quotes_attachment/2/3/test.txt')
            ->willReturn('file content');
        $this->attachmentContentFactory->expects($this->once())
            ->method('create')
            ->willReturn($attachmentContent);
        $attachment->expects($this->once())->method('getFileType')->willReturn('text/plain');
        $attachment->expects($this->once())->method('getFileName')->willReturn('test.txt');
        $attachmentCollection->expects($this->once())->method('getAllIds')->willReturn([1]);

        $this->model->get($attachmentIds);
    }
}
