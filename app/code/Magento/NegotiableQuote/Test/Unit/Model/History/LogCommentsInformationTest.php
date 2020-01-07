<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\History;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for LogCommentsInformation.
 */
class LogCommentsInformationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Status\LabelProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogCommentsInformation
     */
    private $logCommentsInformation;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->commentManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\CommentManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->commentRepository = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\CommentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->labelProvider = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Status\LabelProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->logCommentsInformation = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\History\LogCommentsInformation::class,
            [
                'commentManagement' => $this->commentManagement,
                'commentRepository' => $this->commentRepository,
                'labelProvider' => $this->labelProvider,
            ]
        );
    }

    /**
     * Test for getLogAuthor.
     *
     * @return void
     */
    public function testGetLogAuthor()
    {
        $authorId = 1;
        $quoteId = 2;
        $authorName = 'name';
        $historyLog = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\HistoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $historyLog->expects($this->atLeastOnce())->method('getAuthorId')->willReturn($authorId);
        $historyLog->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn(\Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_CLOSED);
        $this->commentManagement->expects($this->atLeastOnce())
            ->method('checkCreatorLogExists')
            ->with($authorId)
            ->willReturn(true);
        $this->commentManagement->expects($this->atLeastOnce())
            ->method('getCreatorName')
            ->willReturn($authorName);
        $historyLog->expects($this->atLeastOnce())->method('getIsSeller')->willReturn(false);

        $this->assertEquals($authorName, $this->logCommentsInformation->getLogAuthor($historyLog, $quoteId));
    }

    /**
     * Test for getLogAuthor() with author name='System'.
     *
     * @param int|null $authorId
     * @param bool $isCreatorLogExists
     * @param int $getStatusInvokesCount
     * @param int $checkCreatorLogExistsInvokesCount
     * @return void
     * @dataProvider getLogAuthorWithSystemNameDataProvider
     */
    public function testGetLogAuthorWithSystemName(
        $authorId,
        $isCreatorLogExists,
        $getStatusInvokesCount,
        $checkCreatorLogExistsInvokesCount
    ) {
        $quoteId = 2;
        $historyLog = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\HistoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $historyLog->expects($this->atLeastOnce())->method('getAuthorId')->willReturn($authorId);
        $historyLog->expects($this->exactly($getStatusInvokesCount))
            ->method('getStatus')
            ->willReturn(\Magento\NegotiableQuote\Api\Data\HistoryInterface::STATUS_CLOSED);
        $this->commentManagement->expects($this->exactly($checkCreatorLogExistsInvokesCount))
            ->method('checkCreatorLogExists')
            ->with($authorId)
            ->willReturn($isCreatorLogExists);

        $this->assertEquals(__('System'), $this->logCommentsInformation->getLogAuthor($historyLog, $quoteId));
    }

    /**
     * Test for getCommentAttachments().
     *
     * @return void
     */
    public function testGetCommentAttachments()
    {
        $commentId = 1;
        $commentAttachments = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentManagement->expects($this->atLeastOnce())->method('getCommentAttachments')->with($commentId)
            ->willReturn($commentAttachments);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class,
            $this->logCommentsInformation->getCommentAttachments($commentId)
        );
    }

    /**
     * Test for getCommentText().
     *
     * @param string|null $commentText
     * @dataProvider getCommentTextDataProvider
     * @return void
     */
    public function testGetCommentText($commentText)
    {
        $commentId = 1;
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CommentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $comment->expects($this->atLeastOnce())->method('getComment')->willReturn($commentText);
        $this->commentRepository->expects($this->atLeastOnce())->method('get')->with($commentId)->willReturn($comment);

        $this->assertEquals($commentText, $this->logCommentsInformation->getCommentText($commentId));
    }

    /**
     * Test for getStatusLabel().
     *
     * @return void
     */
    public function testGetStatusLabel()
    {
        $label = 'label';
        $this->labelProvider->expects($this->atLeastOnce())->method('getLabelByStatus')->willReturn($label);

        $this->assertEquals($label, $this->logCommentsInformation->getStatusLabel('status'));
    }

    /**
     * DataProvider for testGetLogAuthor().
     *
     * @return array
     */
    public function getLogAuthorWithSystemNameDataProvider()
    {
        return [
            [1, false, 1, 1],
            [null, true, 0, 0],
            [null, false, 0, 0]
        ];
    }

    /**
     * DataProvider for testGetCommentText().
     *
     * @return array
     */
    public function getCommentTextDataProvider()
    {
        return [
            ['comment'],
            [null]
        ];
    }
}
