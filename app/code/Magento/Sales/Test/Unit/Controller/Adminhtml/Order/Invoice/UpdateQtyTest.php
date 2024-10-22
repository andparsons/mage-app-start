<?php
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class UpdateQtyTest
 *
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateQtyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty
     */
    protected $controller;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceServiceMock;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewMock->expects($this->any())->method('loadLayout')->will($this->returnSelf());

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->pageConfigMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getTitle',
                    'getSession',
                    'getHelper',
                    'getActionFlag',
                    'getMessageManager',
                    'getResultRedirectFactory',
                    'getView'
                ]
            )
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));
        $contextMock->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));

        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRawFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultJsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->invoiceServiceMock = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'invoiceService' => $this->invoiceServiceMock
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute()
    {
        $orderId = 1;
        $invoiceData = ['comment_text' => 'test'];
        $response = 'test data';

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('invoice', [])
            ->will($this->returnValue($invoiceData));

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('getTotalQty')
            ->willReturn(2);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $this->invoiceServiceMock->expects($this->once())
            ->method('prepareInvoice')
            ->with($orderMock, [])
            ->willReturn($invoiceMock);

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Sales\Model\Order::class)
            ->willReturn($orderMock);

        $blockItemMock = $this->getMockBuilder(\Magento\Sales\Block\Order\Items::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockItemMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($response));

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('order_items')
            ->will($this->returnValue($blockItemMock));

        $this->resultPageMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));
        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($this->pageConfigMock));

        $this->pageConfigMock->expects($this->once())->method('getTitle')->will($this->returnValue($this->titleMock));

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->resultPageMock));

        $resultRaw = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultRaw->expects($this->once())->method('setContents')->with($response);

        $this->resultRawFactoryMock->expects($this->once())->method('create')->will($this->returnValue($resultRaw));

        $this->assertSame($resultRaw, $this->controller->execute());
    }

    /**
     * Test execute model exception
     *
     * @return void
     */
    public function testExecuteModelException()
    {
        $message = 'The order no longer exists.';
        $response = ['error' => true, 'message' => $message];

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Sales\Model\Order::class)
            ->willReturn($orderMock);

        $this->titleMock->expects($this->never())
            ->method('prepend')
            ->with('Invoices');

        /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject */
        $resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultJsonMock->expects($this->once())->method('setData')->with($response);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultJsonMock));

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute exception
     *
     * @return void
     */
    public function testExecuteException()
    {
        $message = 'The order no longer exists.';
        $response = ['error' => true, 'message' => $message];

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Sales\Model\Order::class)
            ->willReturn($orderMock);

        $this->titleMock->expects($this->never())
            ->method('prepend')
            ->with('Invoices');

        /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject */
        $resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultJsonMock->expects($this->once())->method('setData')->with($response);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultJsonMock));

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }
}
