<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\ResourceModel;

/**
 * Class CommentTest
 * @package Magento\NegotiableQuote\Test\Unit\Model\ResourceModel
 */
class CommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $comment;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->context->expects($this->any())->method('getResources')->willReturn($resource);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->comment = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment::class,
            [
                'context' => $this->context,
            ]
        );
    }

    /**
     * Test saveNegotiatedQuoteData()
     */
    public function testSaveCommentData()
    {
        $comment = $this->createMock(\Magento\NegotiableQuote\Model\Comment::class);
        $comment->expects($this->any())->method('getData')->willReturn(['id' => 1, 'comment' => 'submitted']);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment::class,
            $this->comment->saveCommentData($comment)
        );
    }

    /**
     * Test saveNegotiatedQuoteData() with exception
     */
    public function testSaveCommentDataException()
    {
        $comment = $this->createMock(\Magento\NegotiableQuote\Model\Comment::class);
        $this->connection->expects($this->any())
            ->method('insertOnDuplicate')
            ->willThrowException(new \Exception(''));
        $comment->expects($this->any())->method('getData')->willReturn(['id' => 1, 'comment' => 'submitted']);

        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->comment->saveCommentData($comment);
    }
}
