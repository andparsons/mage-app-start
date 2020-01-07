<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Test for \Magento\NegotiableQuote\Model\CommentRepository class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentResource;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Model\Comment\SearchProvider
     */
    private $searchProvider;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentRepository
     */
    protected $commentRepository;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->commentResource = $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commentFactory = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $comment->expects($this->any())->method('load')->will($this->returnSelf());
        $comment->expects($this->any())->method('getId')->willReturn(14);
        $comment->expects($this->any())->method('delete')->will($this->returnSelf());

        $this->searchProvider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment\SearchProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $this->commentFactory->expects($this->any())->method('create')->willReturn($comment);

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->commentRepository = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\CommentRepository::class,
            [
                'commentResource' => $this->commentResource,
                'commentFactory'  => $this->commentFactory,
                'logger'          => $this->logger,
                'searchProvider'  => $this->searchProvider,
            ]
        );
    }

    /**
     * Test for method Save
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSave()
    {
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CommentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $comment->expects($this->any())->method('getEntityId')->willReturn(1);
        $this->commentResource->expects($this->once())->method('saveCommentData');
        $this->assertEquals(true, $this->commentRepository->save($comment));
    }

    /**
     * Test for method save
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveEmpty()
    {
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CommentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $comment->expects($this->any())->method('getEntityId')->willReturn(null);
        $this->commentResource->expects($this->never())->method('saveCommentData');
        $this->assertEquals(false, $this->commentRepository->save($comment));
    }

    /**
     * Test for method save with exeption
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveExeption()
    {
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CommentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $comment->expects($this->any())->method('getEntityId')->willReturn(1);
        $this->commentResource->expects($this->once())
            ->method('saveCommentData')->willThrowException(new \Exception());
        $this->logger->expects($this->once())->method('critical');
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->assertEquals(false, $this->commentRepository->save($comment));
    }

    /**
     * Test for method \Magento\NegotiableQuote\Model\CommentRepository::get
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGet()
    {
        $id = 14;
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals($this->commentRepository->get($id), $comment);
    }

    /**
     * Test for method \Magento\NegotiableQuote\Model\CommentRepository::delete
     *
     * @return void
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testDelete()
    {
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentResource->expects($this->once())
            ->method('delete')
            ->with($comment)
            ->willReturnSelf();

        $this->assertTrue($this->commentRepository->delete($comment));
    }

    /**
     * Test for method \Magento\NegotiableQuote\Model\CommentRepository::deleteById
     *
     * @return void
     */
    public function testDeleteById()
    {
        $commentId = 14;
        $this->assertTrue($this->commentRepository->deleteById($commentId));
    }

    /**
     * Test for method \Magento\NegotiableQuote\Model\CommentRepository::getList
     *
     * @return void
     */
    public function testGetList()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResults::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchProvider->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $this->assertEquals($searchResults, $this->commentRepository->getList($searchCriteria));
    }
}
