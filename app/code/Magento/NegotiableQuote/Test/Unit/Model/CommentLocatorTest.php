<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Class for test CommentLocator.
 */
class CommentLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentCollectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteCollectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCollection;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentLocator
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->commentCollectionFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->commentManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\CommentManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCommentAttachments'])
            ->getMockForAbstractClass();
        $this->quoteCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection::class
        )
            ->setMethods(['addFieldToFilter', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteCollectionFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteCollectionFactory->expects($this->once())
            ->method('create')->willReturn($this->quoteCollection);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\CommentLocator::class,
            [
                'collectionFactory' => $this->commentCollectionFactory,
                'commentManagement' => $this->commentManagement,
                'negotiableQuoteCollectionFactory' => $this->negotiableQuoteCollectionFactory
            ]
        );
    }

    /**
     * Test for getListForQuote() method.
     *
     * @return void
     */
    public function testGetListForQuote()
    {
        $quoteId = 1;
        $this->quoteCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->withConsecutive(['entity_id', $quoteId])
            ->willReturnSelf();
        $this->quoteCollection->expects($this->once())
            ->method('getSize')->willReturn(1);
        $commentCollection = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'load'])
            ->getMock();
        $this->commentCollectionFactory->expects($this->atLeastOnce())
            ->method('create')->willReturn($commentCollection);
        $commentCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->withConsecutive(['parent_id', $quoteId])
            ->willReturnSelf();
        $comment = $this->getComment();
        $commentCollection->addItem($comment);
        $attachmentCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $attachmentCollection->expects($this->once())->method('getItems')->willReturn([]);
        $this->commentManagement->expects($this->atLeastOnce())
            ->method('getCommentAttachments')
            ->willReturn($attachmentCollection);
        $expected = [4 => $comment];
        $this->assertEquals($expected, $this->model->getListForQuote($quoteId));
    }

    /**
     * Test for getListForQuote() method with exception of not existed quote.
     *
     * @return void
     */
    public function testGetListForQuoteWithException()
    {
        $quoteId = 1;
        $this->quoteCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->withConsecutive(['entity_id', $quoteId])
            ->willReturnSelf();
        $this->quoteCollection->expects($this->once())
            ->method('getSize')->willReturn(0);
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->model->getListForQuote($quoteId);
    }

    /**
     * Get mock comment for qoute.
     *
     * @return \Magento\NegotiableQuote\Api\Data\CommentInterface
     */
    private function getComment()
    {
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $comment->expects($this->atLeastOnce())->method('getId')->willReturn(4);
        $comment->expects($this->atLeastOnce())->method('getEntityId')->willReturn(4);
        return $comment;
    }
}
