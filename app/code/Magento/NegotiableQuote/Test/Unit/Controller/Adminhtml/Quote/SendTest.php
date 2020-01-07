<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Send
     */
    private $controller;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

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
     * @var \Magento\NegotiableQuote\Model\CommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentRepository;

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
        $this->resource = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->resource->expects($this->exactly(4))
            ->method('getParam')
            ->withConsecutive(['quote_id'], ['comment'], ['quote'], ['dataSend'])
            ->willReturnOnConsecutiveCalls(1, [], json_encode([]), 'comment text');
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->fileProcessor = $this->getMockBuilder(\Magento\NegotiableQuote\Controller\FileProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFiles'])
            ->getMockForAbstractClass();
        $this->fileProcessor->expects($this->atLeastOnce())->method('getFiles')->willReturn([]);
        $this->resultPageFactory =
            $this->createPartialMock(\Magento\Framework\View\Result\PageFactory::class, ['create']);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $redirect = $this->createPartialMock(
            \Magento\Framework\Controller\ResultInterface::class,
            ['setData', 'setHttpResponseCode', 'setHeader', 'renderResult']
        );
        $redirect->expects($this->any())->method('setData')->will($this->returnSelf());
        $this->resultFactory->expects($this->any())->method('create')->will($this->returnValue($redirect));
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $negotiableQuoteRepository =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class);
        $this->quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getExtensionAttributes', 'getId']);
        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quoteNegotiation->expects($this->any())->method('getIsRegularQuote')->will($this->returnValue(true));
        $quoteNegotiation->expects($this->any())->method('getStatus')->willReturn(NegotiableQuote::STATUS_CREATED);
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

        $this->negotiableQuoteManagement =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class);
        $this->commentRepository =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\CommentRepositoryInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->commentRepository->expects($this->any())->method('create')->will($this->returnValue(true));
        $restriction = $this->createMock(\Magento\NegotiableQuote\Model\Restriction\Admin::class);
        $restriction->setQuote($this->quote);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Send::class,
            [
                'request' => $this->resource,
                'resultFactory' => $this->resultFactory,
                'messageManager' => $this->messageManager,
                'logger' => $logger,
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $restriction,
                'dataObjectHelper' => $this->dataObjectHelper,
                'fileProcessor' => $this->fileProcessor,
                'negotiableQuoteRepository' => $negotiableQuoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement
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
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->negotiableQuoteManagement->expects($this->any())
            ->method('adminSend')->willThrowException(new \Exception());

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }
}
