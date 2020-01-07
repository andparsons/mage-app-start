<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory;

/**
 * Class AbstractMassActionTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractMassActionTest extends \PHPUnit\Framework\TestCase
{
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
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteCollectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\AbstractMassAction
     */
    private $massAction;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->filter = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);
        $this->resultRedirectFactory = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->negotiableQuoteCollectionFactory =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Quote\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->negotiableQuoteCollection =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Quote\Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->messageManager =
            $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['addError', 'addException'])
                ->getMockForAbstractClass();
        $this->redirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $redirectUrl = '*/*/index';
        $this->negotiableQuoteCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->negotiableQuoteCollection);

        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirect);
        $this->redirect->expects($this->once())->method('setPath')->with($redirectUrl)->willReturnSelf();
        $this->massAction = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\MassDeclineCheck::class,
            [
                'filter' => $this->filter,
                'collectionFactory' => $this->negotiableQuoteCollectionFactory,
                'resultFactory' => $this->resultFactory,
                'messageManager' => $this->messageManager
            ]
        );
    }

    /**
     * Test execute with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $phrase = new \Magento\Framework\Phrase('Something went wrong.');
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->filter->expects($this->once())
            ->method('getCollection')
            ->with($this->negotiableQuoteCollection)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addError')->willReturnSelf();
        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Redirect::class, $this->massAction->execute());
    }

    /**
     * Test execute with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $phrase = new \Magento\Framework\Phrase('Something went wrong. Please try again later.');
        $exception = new \Exception($phrase);
        $this->filter->expects($this->once())
            ->method('getCollection')
            ->with($this->negotiableQuoteCollection)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addException')
            ->with($exception, $phrase)
            ->willReturnSelf();
        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Redirect::class, $this->massAction->execute());
    }
}
