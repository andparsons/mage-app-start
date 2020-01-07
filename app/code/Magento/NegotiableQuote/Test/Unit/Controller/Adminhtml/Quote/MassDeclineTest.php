<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDeclineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\MassDecline
     */
    private $massAction;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteCollectionMock;

    /**
     * @var QuoteCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagementMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->negotiableQuoteCollectionMock =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->negotiableQuoteCollectionFactoryMock =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $redirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirectMock);
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirectMock);

        $this->filterMock = $this->getMockBuilder(\Magento\Ui\Component\MassAction\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->negotiableQuoteCollectionMock)
            ->willReturnArgument(0);
        $this->negotiableQuoteCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->negotiableQuoteCollectionMock);
        $this->negotiableQuoteManagementMock =
            $this->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->restriction = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->massAction = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\MassDecline::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'resultFactory' => $this->resultFactory,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->negotiableQuoteCollectionFactoryMock,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagementMock,
                'restriction' => $this->restriction,
                'quoteRepository' => $this->quoteRepository,
            ]
        );
    }

    /**
     * Test for method execute with available for declining quote.
     */
    public function testExecuteAvailable()
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->negotiableQuoteCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$quote]));
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($quote);
        $this->restriction->expects($this->once())->method('canDecline')->willReturn(true);

        $this->negotiableQuoteManagementMock->expects($this->once())->method('decline');

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->massAction->execute());
    }

    /**
     * Test for method execute with unavailable for declining quote.
     */
    public function testExecuteUnavailable()
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->negotiableQuoteCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$quote]));
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($quote);
        $this->restriction->expects($this->once())->method('canDecline')->willReturn(false);

        $this->negotiableQuoteManagementMock->expects($this->never())->method('decline');

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('*/*/index')
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->massAction->execute());
    }
}
