<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;

/**
 * Class MassDeclineCheckTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDeclineCheckTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $actionName = 'DeclineCheck';

    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\Mass|\PHPUnit_Framework_MockObject_MockObject
     */
    private $massAction;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirect;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteCollection;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        if (empty($this->actionName)) {
            return;
        }
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);

        $this->negotiableQuoteCollection =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $negotiableQuoteCollectionFactory =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->redirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirect);
        $this->resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->filter = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->filter->expects($this->once())
            ->method('getCollection')
            ->with($this->negotiableQuoteCollection)
            ->willReturnArgument(0);
        $negotiableQuoteCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->negotiableQuoteCollection);

        $negotiableQuoteManagement = $this->getMockForAbstractClass(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class,
            [],
            '',
            false
        );
        $resultJsonFactory = $this->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->resultJson = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $resultJsonFactory->expects($this->any())->method('create')->willReturn($this->resultJson);

        $this->quoteRepository = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->restriction = $this->getMockForAbstractClass(RestrictionInterface::class, [], '', false);

        $this->massAction = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\MassDeclineCheck::class,
            [
                'filter' => $this->filter,
                'collectionFactory' => $negotiableQuoteCollectionFactory,
                'negotiableQuoteManagement' => $negotiableQuoteManagement,
                'resultJsonFactory' => $resultJsonFactory,
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $this->restriction,
                'resultFactory' => $this->resultFactory,
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
        $quoteId = 123;
        $quote = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->quoteRepository->expects($this->any())->method('get')->willReturn($quote);
        $testData = [$this->createMock(\Magento\Quote\Model\Quote::class)];

        $this->restriction->expects($this->any())->method('canDecline')->willReturn(true);
        $response = new \Magento\Framework\DataObject();
        $response->setData('items', [$quoteId]);
        $this->resultJson->expects($this->once())->method('setData')->with($response)->willReturnSelf();

        $this->negotiableQuoteCollection
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($testData));

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->massAction->execute());
    }
}
