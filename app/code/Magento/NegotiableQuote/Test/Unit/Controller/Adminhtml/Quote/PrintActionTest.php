<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\PrintAction
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPage;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageConfig;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFlag;

    /**
     * @var int
     */
    private $quoteId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quoteId = 42;

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam', 'initForward', 'setActionName', 'setDispatched'])
            ->getMockForAbstractClass();

        $this->resultFactory = $this->createMock(
            \Magento\Framework\Controller\ResultFactory::class
        );
        $this->resultPage = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['addBreadcrumb', 'getConfig']
        );

        $this->pageConfig = $this->createPartialMock(
            \Magento\Framework\View\Page\Config::class,
            ['getTitle']
        );

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->negotiableQuoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->session = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsUrlNotice'])
            ->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->actionFlag = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\PrintAction::class,
            [
                'resultFactory' => $this->resultFactory,
                'logger' => $this->logger,
                'quoteRepository' => $this->quoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                '_request' => $this->request,
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
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->resultPage);
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($this->quoteId);
        $this->resultPage->expects($this->any())->method('addBreadcrumb')->willReturnSelf();

        $this->resultPage->expects($this->once())->method('getConfig')->willReturn($this->pageConfig);

        $title = $this->createMock(
            \Magento\Framework\View\Page\Title::class
        );
        $this->pageConfig->expects($this->once())->method('getTitle')->willReturn($title);
        $title->expects($this->once())->method('prepend');

        $this->controller->execute();
    }

    /**
     * Test for method execute with null quote.
     *
     * @return void
     */
    public function testExecuteNoQuote()
    {
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn(null);

        $this->session->expects($this->any())->method('setIsUrlNotice');
        $this->request->expects($this->once())->method('initForward');
        $this->request->expects($this->once())->method('setActionName');
        $this->request->expects($this->once())->method('setDispatched');

        $this->controller->execute();
    }

    /**
     * Test for method execute throwing NoSuchEntityException.
     *
     * @return void
     */
    public function testExecuteNoSuchEntityException()
    {
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->resultPage);
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($this->quoteId);
        $this->resultPage->expects($this->any())->method('addBreadcrumb')->willReturnSelf();

        $exception = new NoSuchEntityException();
        $this->resultPage->expects($this->once())->method('getConfig')->willThrowException($exception);
        $this->messageManager->expects($this->any())->method('addError')->with('Quote not found');
        $this->actionFlag->expects($this->any())->method('set');

        $this->controller->execute();
    }

    /**
     * Test for method execute throwing Exception.
     *
     * @return void
     */
    public function testExecuteException()
    {
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->resultPage);
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($this->quoteId);

        $exception = new \Exception();
        $this->resultPage->expects($this->once())->method('addBreadcrumb')->willThrowException($exception);
        $this->logger->expects($this->any())
            ->method('critical')
            ->with($exception);
        $this->messageManager->expects($this->any())->method('addError')->with('Method is not exists');

        $this->controller->execute();
    }
}
