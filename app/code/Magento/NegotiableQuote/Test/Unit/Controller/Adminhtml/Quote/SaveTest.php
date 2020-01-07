<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment as CommentAttachmentResource;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Unit test for Save.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Save
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterfaceFactory|
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $commentAttachmentFactory;

    /**
     * @var CommentAttachmentResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentAttachmentResource;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Controller\FileProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessor;

    /**
     * Set up.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->resultPageFactory = $this->createPartialMock(
            \Magento\Framework\View\Result\PageFactory::class,
            ['create']
        );
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->resource->expects($this->at(0))->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->resource->expects($this->at(1))->method('getParam')->with('quote')->will($this->returnValue([]));
        $this->resource->expects($this->at(2))->method('getParam')->with('dataSend')->willReturn(json_encode([]));
        $this->fileProcessor = $this->getMockBuilder(\Magento\NegotiableQuote\Controller\FileProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFiles'])
            ->getMockForAbstractClass();
        $this->fileProcessor->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);
        $redirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $redirect = $this->createPartialMock(
            \Magento\Framework\Controller\ResultInterface::class,
            ['setData', 'setHttpResponseCode', 'setHeader', 'renderResult']
        );
        $redirect->expects($this->any())->method('setData')->will($this->returnSelf());
        $redirectFactory->expects($this->any())->method('create')->will($this->returnValue($redirect));
        $this->dataObjectHelper = $this->createMock(
            \Magento\Framework\Api\DataObjectHelper::class
        );
        $negotiableQuoteRepository = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class
        );
        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes', 'getId'])
            ->getMock();
        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quoteNegotiation->expects($this->any())->method('getIsRegularQuote')->will($this->returnValue(true));
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')
            ->will($this->returnValue($quoteNegotiation));
        $this->quote->expects($this->any())->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($this->quote));
        $this->negotiableQuoteManagement = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        );
        $this->commentAttachmentFactory = $this->createPartialMock(
            \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterfaceFactory::class,
            ['create']
        );
        $this->commentAttachmentResource = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment::class,
            ['delete']
        );
        $this->commentManagement = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\CommentManagement::class,
            ['hasDraftComment', 'getQuoteComments', 'getCommentAttachments']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Save::class,
            [
                'messageManager' => $this->messageManager,
                'actionFlag' => $actionFlag,
                'resultFactory' => $redirectFactory,
                'request' => $this->resource,
                'logger' => $logger,
                'quoteRepository' => $this->quoteRepository,
                'dataObjectHelper' => $this->dataObjectHelper,
                'negotiableQuoteRepository' => $negotiableQuoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'attachmentInterfaceFactory' => $this->commentAttachmentFactory,
                'commentAttachmentResource' => $this->commentAttachmentResource,
                'fileProcessor' => $this->fileProcessor,
                'commentManagement' => $this->commentManagement
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->resource->expects($this->at(3))->method('getParam')->with('comment')->willReturn([]);
        $this->resource->expects($this->at(4))->method('getParam')->with('delFiles')->will($this->returnValue('1,2'));
        $page = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['setActiveMenu', 'addBreadcrumb', 'getConfig']
        );
        $title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $page->expects($this->any())->method('getConfig')->will($this->returnValue($config));
        $config->expects($this->any())->method('getTitle')->will($this->returnValue($title));
        $this->quote->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
        $this->negotiableQuoteManagement->expects($this->any())
            ->method('saveAsDraft')->with(1, [], ['message' => [], 'files' => []])->willReturnSelf();
        $commentAttachment = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['load', 'getFileName', 'getAttachmentId']
        );
        $this->commentAttachmentFactory
            ->expects($this->atLeastOnce())->method('create')->willReturn($commentAttachment);
        $commentAttachment->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $commentAttachment->expects($this->atLeastOnce())->method('getFileName')->willReturn('filename.doc');
        $commentAttachment->expects($this->atLeastOnce())->method('getAttachmentId')->willReturn(2);

        $this->commentAttachmentResource->expects($this->atLeastOnce())->method('delete');
        $this->commentManagement->expects($this->once())
            ->method('hasDraftComment')
            ->with(1)
            ->will($this->returnValue(true));
        $comment = $this->getMockForAbstractClass(
            \Magento\NegotiableQuote\Api\Data\CommentInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getEntityId']
        );
        $comment->expects($this->once())->method('getEntityId')->willReturn(1);
        $commentCollection = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class,
            ['getFirstItem']
        );
        $commentCollection->expects($this->once())->method('getFirstItem')->willReturn($comment);
        $this->commentManagement->expects($this->once())
            ->method('getQuoteComments')
            ->with(1, true)
            ->will($this->returnValue($commentCollection));
        $this->commentManagement->expects($this->once())
            ->method('getCommentAttachments')
            ->with(1)
            ->will($this->returnValue([$commentAttachment]));

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->messageManager->expects($this->once())->method('addError');
        $this->negotiableQuoteManagement->expects($this->any())
            ->method('saveAsDraft')->willThrowException(new \Exception());

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $this->resource->expects($this->at(4))->method('getParam')->with('delFiles')->will($this->returnValue('1,2'));
        $this->messageManager->expects($this->once())->method('addError');
        $commentAttachment = $this->getMockForAbstractClass(
            \Magento\Framework\Model\AbstractModel::class,
            [],
            '',
            false,
            false,
            true,
            ['load']
        );
        $this->commentAttachmentFactory
            ->expects($this->atLeastOnce())->method('create')->willReturn($commentAttachment);
        $commentAttachment->expects($this->atLeastOnce())
            ->method('load')
            ->willThrowException(new NoSuchEntityException());

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }
}
