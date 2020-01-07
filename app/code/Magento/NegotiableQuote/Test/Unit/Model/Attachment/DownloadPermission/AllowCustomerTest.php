<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Attachment\DownloadPermission;

/**
 * Test for Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowCustomer method.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AllowCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentAttachmentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentAttachmentFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowCustomer
     */
    private $allowCustomer;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentAttachment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attachment;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attachment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\CommentAttachment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentAttachmentFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\CommentAttachmentFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->commentRepository = $this->getMockBuilder(\Magento\NegotiableQuote\Model\CommentRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->structureMock = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->allowCustomer = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowCustomer::class,
            [
                'userContext' => $this->userContext,
                'commentAttachmentFactory' => $this->commentAttachmentFactory,
                'quoteRepository' => $this->quoteRepository,
                'commentRepository' => $this->commentRepository,
                'structure' => $this->structureMock
            ]
        );
    }

    /**
     * Test isAllowed method.
     *
     * @return void
     */
    public function testIsAllowed()
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentId'])
            ->getMock();
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn(333);
        $this->commentAttachmentFactory->expects($this->once())->method('create')->willReturn($this->attachment);
        $this->attachment->expects($this->once())->method('load')->willReturnSelf();
        $this->structureMock->expects($this->once())
            ->method('getAllowedChildrenIds')
            ->willReturn([1, 2]);
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($quote);
        $quote->expects($this->once())->method('getCustomer')->willReturnSelf();
        $quote->expects($this->once())->method('getId')->willReturn(333);
        $this->commentRepository->expects($this->once())->method('get')->willReturn($comment);
        $comment->expects($this->atLeastOnce())->method('getParentId')->willReturn(3);

        $this->assertTrue($this->allowCustomer->isAllowed(1));
    }

    /**
     * Test isAllowed method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testIsAllowedWithException()
    {
        $exceptionMessage = 'An error occurred.';
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__($exceptionMessage));
        $this->commentAttachmentFactory->expects($this->once())->method('create')->willReturn($this->attachment);
        $this->attachment->expects($this->once())->method('load')->willThrowException($exception);
        $this->assertTrue($this->allowCustomer->isAllowed(1));
    }
}
