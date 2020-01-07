<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * Class DeclineTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeclineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Decline|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decline;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            ['getParam'],
            '',
            false,
            false,
            true,
            []
        );
        $this->resultPageFactory =
            $this->createPartialMock(\Magento\Framework\View\Result\PageFactory::class, ['create']);
        $this->resultRedirectFactory =
            $this->createPartialMock(\Magento\Backend\Model\View\Result\RedirectFactory::class, ['create']);
        $this->resultRedirect =
            $this->createPartialMock(\Magento\Framework\Controller\Result\Redirect::class, ['setPath']);
        $this->logger = $this->getMockForAbstractClass(
            \Psr\Log\LoggerInterface::class,
            ['critical'],
            '',
            false,
            false,
            true,
            []
        );
        $this->quoteRepository = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            ['get'],
            '',
            false,
            false,
            true,
            []
        );
        $this->negotiableQuoteManagement =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class);
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            ['addError'],
            '',
            false,
            false,
            true,
            []
        );
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->decline = $this->objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Decline::class,
            [
                'request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'resultPageFactory' => $this->resultPageFactory,
                'logger' => $this->logger,
                'quoteRepository' => $this->quoteRepository,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement
            ]
        );
    }

    /**
     * Test form method execute().
     */
    public function testExecute()
    {
        $this->createQuote();
        $this->negotiableQuoteManagement->expects($this->once())->method('decline');
        $this->getRedirect();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->decline->execute());
    }

    /**
     * Test form method execute() throwing exception.
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception('test message');
        $this->createQuote();
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('decline')->willThrowException($exception);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->messageManager->expects($this->any())->method('addError');
        $this->getRedirect();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->decline->execute());
    }

    /**
     * Makes sure $quote evaluates to true.
     */
    private function createQuote()
    {
        $isRegular = true;
        $negotiableQuote =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class, ['getIsRegularQuote']);
        $negotiableQuote->expects($this->any())
            ->method('getIsRegularQuote')
            ->will($this->returnValue($isRegular));
        $extension = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getNegotiableQuote']
        );
        $extension->expects($this->any())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $this->quote = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            ['getExtensionAttributes', 'getId'],
            '',
            false,
            false,
            true,
            ['getAppliedRuleIds']
        );
        $this->quote->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extension);
        $this->quote->expects($this->any())
            ->method('getAppliedRuleIds')
            ->will($this->returnValue('1'));
        $this->quoteRepository->expects($this->any())
            ->method('get')
            ->willReturn($this->quote);
    }

    /**
     * Makes sure Magento\NegotiableQuote\Controller\Adminhtml\Quote::getRedirect() works properly.
     */
    private function getRedirect()
    {
        $quoteId = 1;
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);
        $this->quote->expects($this->any())->method('getId')->willReturn($quoteId);
        $this->resultRedirect->expects($this->any())
            ->method('setPath')
            ->with('quotes/quote/view', ['quote_id' =>$quoteId])
            ->willReturnSelf();
    }
}
