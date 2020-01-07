<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote;

use Magento\Framework\DataObject;

/**
 * Class for test Comments class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentManagement;

    /**
     * @var \Magento\Framework\File\Size|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSize;

    /**
     * @var \Magento\NegotiableQuote\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteConfig;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\Creator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creator;

    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Comments
     */
    private $comments;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentManagement = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\CommentManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fileSize = $this->getMockBuilder(\Magento\Framework\File\Size::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creator = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Creator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteConfig = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->comments = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\Comments::class,
            [
                '_urlBuilder' => $this->urlBuilder,
                'quoteRepository' => $this->quoteRepository,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'commentManagement' => $this->commentManagement,
                'fileSize' => $this->fileSize,
                'negotiableQuoteConfig' => $this->negotiableQuoteConfig,
                'creator' => $this->creator,
            ]
        );
    }

    /**
     * Test getQuoteHelper.
     *
     * @return void
     */
    public function testGetQuoteHelper()
    {
        $this->assertInstanceOf(\Magento\NegotiableQuote\Helper\Quote::class, $this->comments->getQuoteHelper());
    }

    /**
     * Test getQuoteComments.
     *
     * @param int|null $quoteId
     * @param array $comments
     * @dataProvider dataProviderGetQuoteComments
     * @return void
     */
    public function testGetQuoteComments($quoteId, array $comments)
    {
        $this->quote->expects($this->once())->method('getEntityId')->willReturn($quoteId);
        $this->negotiableQuoteHelper->expects($this->atLeastOnce())->method('resolveCurrentQuote')
            ->willReturnOnConsecutiveCalls($this->quote, $this->quote, null, null);
        $this->commentManagement->expects($this->once())->method('getQuoteComments')->willReturn($comments);

        $this->assertEquals($comments, $this->comments->getQuoteComments());
    }

    /**
     * Test getDraftComment.
     *
     * @param int|null $quoteId
     * @param bool $hasDraftComment
     * @param \Magento\Framework\DataObject|null $draftComment
     * @dataProvider dataProviderGetDraftComment
     * @return void
     */
    public function testGetDraftComment($quoteId, $hasDraftComment, $draftComment)
    {
        $this->quote->expects($this->atLeastOnce())->method('getEntityId')->willReturn($quoteId);
        $this->negotiableQuoteHelper->expects($this->atLeastOnce())->method('resolveCurrentQuote')
            ->willReturnOnConsecutiveCalls($this->quote, $this->quote, $this->quote, $this->quote, $this->quote, null);
        $commentCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentManagement->expects($this->once())->method('hasDraftComment')->willReturn($hasDraftComment);
        if ($hasDraftComment) {
            $this->commentManagement->expects($this->once())
                ->method('getQuoteComments')
                ->willReturn($commentCollection);
            $commentCollection->expects($this->once())->method('getFirstItem')->willReturn($draftComment);
        }

        $this->assertEquals($draftComment, $this->comments->getDraftComment());
    }

    /**
     * DataProvider getDraftComments.
     *
     * @return array
     */
    public function dataProviderGetDraftComment()
    {
        return [
            [1, true, new DataObject(['comment' => 'comment 1'])],
            [1, false, null],
            [null, false, null]
        ];
    }

    /**
     * Test getCommentAttachments.
     *
     * @param int $commentId
     * @param \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection|array $commentAttachments
     * @param int $calls
     * @dataProvider dataProviderGetCommentAttachments
     * @return void
     */
    public function testGetCommentAttachments($commentId, $commentAttachments, $calls)
    {
        $this->commentManagement->expects($this->exactly($calls))->method('getCommentAttachments')
            ->willReturn($commentAttachments);

        $this->assertEquals($commentAttachments, $this->comments->getCommentAttachments($commentId));
    }

    /**
     * DataProvider getCommentAttachments.
     *
     * @return array
     */
    public function dataProviderGetCommentAttachments()
    {
        $commentAttachments = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        return [
            [1, $commentAttachments, 1],
            [null, [], 0]
        ];
    }

    /**
     * Test getAttachmentUrl.
     *
     * @return void
     */
    public function testGetAttachmentUrl()
    {
        $this->urlBuilder->expects($this->once())->method('getUrl')->willReturn('url');

        $this->assertEquals('url', $this->comments->getAttachmentUrl(1));
    }

    /**
     * Test getCommentAuthor.
     *
     * @return void
     */
    public function testGetCommentCreator()
    {
        $quoteId = 1;
        $creatorId = 17;
        $creatorName = 'Representative';
        $expectedResult = '(Representative)';
        $creatorType = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN;
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->negotiableQuoteHelper->expects($this->once())->method('resolveCurrentQuote')->willReturn($quote);
        /**
         * @var \Magento\NegotiableQuote\Api\Data\CommentInterface|\PHPUnit_Framework_MockObject_MockObject $comment
         */
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CommentInterface::class)
            ->setMethods([
                'getCreatorId',
                'getCreatorType'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $comment->expects($this->exactly(2))->method('getCreatorId')->willReturn($creatorId);
        $comment->expects($this->once())->method('getCreatorType')->willReturn($creatorType);
        $this->creator->expects($this->once())->method('retrieveCreatorName')
            ->with($creatorType, $creatorId, $quoteId)->willReturn($creatorName);
        $this->assertEquals($expectedResult, $this->comments->getCommentCreator($comment));
    }

    /**
     * Test getMaxFileSizeMb.
     *
     * @param int|null $configSize
     * @param float $phpLimitSize
     * @param int $maxFileSize
     * @dataProvider dataProviderGetMaxFileSize
     * @return void
     */
    public function testGetMaxFileSize($configSize, $phpLimitSize, $maxFileSize)
    {
        $this->getMaxFileSizeMb($configSize, $phpLimitSize);
        $this->fileSize->expects($this->once())->method('convertSizeToInteger')->willReturn($maxFileSize);

        $this->assertEquals($maxFileSize, $this->comments->getMaxFileSize());
    }

    /**
     * Test getMaxFileSizeMb.
     *
     * @param int|null $configSize
     * @param float $phpLimitSize
     * @param float $maxFileSize
     * @dataProvider dataProviderGetMaxFileSizeMb
     *
     * @return void
     */
    public function testGetMaxFileSizeMb($configSize, $phpLimitSize, $maxFileSize)
    {
        $this->getMaxFileSizeMb($configSize, $phpLimitSize);

        $this->assertEquals($maxFileSize, $this->comments->getMaxFileSizeMb());
    }

    /**
     * Test getAllowedExtensions.
     *
     * @return void
     */
    public function testGetAllowedExtensions()
    {
        $allowedExtensions = '.txt, .pdf';
        $this->negotiableQuoteConfig->expects($this->once())->method('getAllowedExtensions')
            ->willReturn($allowedExtensions);

        $this->assertEquals($allowedExtensions, $this->comments->getAllowedExtensions());
    }

    /**
     * Test getDeleteUrl.
     *
     * @return void
     */
    public function testGetDeleteUrl()
    {
        $deleteUrl = 'delete_url';
        $this->urlBuilder->expects($this->once())->method('getUrl')->willReturn($deleteUrl);

        $this->assertEquals($deleteUrl, $this->comments->getDeleteUrl());
    }

    /**
     * DataProvider getQuoteComments.
     *
     * @return array
     */
    public function dataProviderGetQuoteComments()
    {
        return [
            [1, ['comment 1', 'comment 2']],
            [null, []]
        ];
    }

    /**
     * DataProvider getMaxFileSize.
     *
     * @return array
     */
    public function dataProviderGetMaxFileSize()
    {
        return [
            [2, 1.11, 1163919],
            [1, 1.55, 1625292.8],
            [null, 1.55, 1625292.8]
        ];
    }

    /**
     * DataProvider getMaxFileSizeMb.
     *
     * @return array
     */
    public function dataProviderGetMaxFileSizeMb()
    {
        return [
            [2, 1.11, 1.11],
            [1, 1.55, 1],
            [null, 1.55, 1.55]
        ];
    }

    /**
     * Set up getMaxFileSizeMb.
     *
     * @param int|null $configSize
     * @param float $phpLimitSize
     *
     * @return void
     */
    private function getMaxFileSizeMb($configSize, $phpLimitSize)
    {
        $this->negotiableQuoteConfig->expects($this->once())->method('getMaxFileSize')->willReturn($configSize);
        $this->fileSize->expects($this->once())->method('getMaxFileSizeInMb')->willReturn($phpLimitSize);
    }
}
