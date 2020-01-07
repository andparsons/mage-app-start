<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\View
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectFactory;

    /**
     * @var \'Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageProvider;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\NegotiableQuote\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->request->expects($this->any())->method('getParam')->with('quote_id')->will($this->returnValue(1));

        $this->redirectFactory =
            $this->createPartialMock(\Magento\Backend\Model\View\Result\RedirectFactory::class, ['create']);
        $redirect = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        $redirect->expects($this->any())
            ->method('setPath')->will($this->returnSelf());
        $this->redirectFactory->expects($this->any())
            ->method('create')->will($this->returnValue($redirect));
        $this->negotiableQuoteManagement =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageProvider =
            $this->createMock(\Magento\NegotiableQuote\Model\Discount\StateChanges\Provider::class);
        $this->cartMock = $this->createPartialMock(\Magento\NegotiableQuote\Model\Cart::class, ['removeAllFailed']);

        $this->negotiableQuoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->setMethods(['isLockMessageDisplayed'])
            ->disableOriginalConstructor()->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['addWarningMessage'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\View::class,
            [
                'resultRedirectFactory' => $this->redirectFactory,
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'logger' => $logger,
                'quoteRepository' => $this->quoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'messageProvider' => $this->messageProvider,
                'messageManager' => $this->messageManager,
                'cart' => $this->cartMock,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper
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
            \Magento\Backend\Model\View\Result\Page::class,
            ['setActiveMenu', 'addBreadcrumb', 'getConfig']
        );
        $this->resultFactory->expects($this->once())->method('create')->will($this->returnValue($page));
        $title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $page->expects($this->any())->method('getConfig')->will($this->returnValue($config));
        $config->expects($this->any())->method('getTitle')->will($this->returnValue($title));
        $quote = $this->createMock(
            \Magento\Quote\Model\Quote::class,
            [
                'getExtensionAttributes',
                'collectTotals'
            ]
        );

        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));
        $this->quoteRepository->expects($this->any())->method('save');

        $this->cartMock->expects($this->once())->method('removeAllFailed');
        $this->messageProvider->expects($this->any())->method('getChangesMessages')->with($quote)
            ->willReturn([1 => 'Message']);

        $this->messageManager->expects($this->exactly(2))->method('addWarningMessage')->willReturnSelf();

        $isLockedMessageDisplayed = true;
        $this->negotiableQuoteHelper->expects(($this->exactly(1)))->method('isLockMessageDisplayed')
            ->willReturn($isLockedMessageDisplayed);

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Page::class, $result);
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $this->quoteRepository->expects($this->any())
            ->method('get')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->negotiableQuoteManagement->expects($this->any())
            ->method('openByMerchant')->willThrowException(new \Exception());
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }
}
