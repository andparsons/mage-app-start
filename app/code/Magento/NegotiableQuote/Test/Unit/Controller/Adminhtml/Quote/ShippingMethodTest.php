<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\ShippingMethod
     */
    private $controller;

    /**
     * @var \'Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $page;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFlag;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->resource = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RawFactory::class,
            ['create']
        );
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->resource->expects($this->at(0))->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $negotiableQuoteRepository = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class
        );
        $this->actionFlag = $this->createMock(
            \Magento\Framework\App\ActionFlag::class
        );
        $negotiableQuoteManagement = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\ShippingMethod::class,
            [
                'request' => $this->resource,
                'messageManager' => $this->messageManager,
                'resultFactory' => $this->resultFactory,
                'logger' => $logger,
                'quoteRepository' => $this->quoteRepository,
                'resultRawFactory' => $resultRawFactory,
                'dataObjectHelper' => $dataObjectHelper,
                'negotiableQuoteRepository' => $negotiableQuoteRepository,
                'negotiableQuoteManagement' => $negotiableQuoteManagement,
                '_actionFlag' => $this->actionFlag
            ]
        );
        $resultRaw = $this->createPartialMock(\Magento\Framework\Controller\Result\Raw::class, ['setContents']);
        $resultRaw->expects($this->any())->method('setContents')->will($this->returnValue($resultRaw));
        $resultRawFactory->expects($this->any())->method('create')->will($this->returnValue($resultRaw));
        $this->page = $this->createMock(
            \Magento\Framework\View\Result\Page::class,
            ['setActiveMenu', 'addHandle', 'getLayout'],
            [],
            '',
            false
        );
    }

    /**
     * Positive execute() test.
     */
    public function testExecute()
    {
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->will($this->returnValue($this->page));
        $this->mockQuote();
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock->expects($this->any())
            ->method('renderElement')
            ->willReturn('ok');
        $this->page->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }

    /**
     * execute() test with exception
     */
    public function testExecuteWithException()
    {
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->will($this->returnValue($this->page));
        $this->mockQuote();
        $this->page->expects($this->any())
            ->method('getLayout')
            ->willThrowException(new \Exception());
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }

    /**
     * Test execute with getQuote NoSuchEntityException thrown.
     *
     * @param NoSuchEntityException|InputException $exception
     * @dataProvider executeDataProvider
     */
    public function testExecuteWithCreateQuoteException($exception)
    {
        $this->quoteRepository->expects($this->any())
            ->method('get')
            ->willThrowException($exception);
        $this->actionFlag->expects($this->once())->method('set')->with('', 'no-dispatch', true);
        $result = $this->controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $result);
    }

    /**
     * Data provider for testExecuteWithCreateQuoteException.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [new NoSuchEntityException()],
            [new InputException()]
        ];
    }

    /**
     * Mock quote.
     */
    private function mockQuote()
    {
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getExtensionAttributes', 'getId', 'getShippingAddress']
        );
        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $quoteNegotiation->expects($this->any())->method('getIsRegularQuote')->will($this->returnValue(true));
        $quoteNegotiation->expects($this->any())->method('getStatus')->willReturn(NegotiableQuote::STATUS_CREATED);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')
            ->will($this->returnValue($quoteNegotiation));
        $quote->expects($this->any())->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));
        $this->quoteRepository->expects($this->any())->method('get')->will($this->returnValue($quote));
    }
}
