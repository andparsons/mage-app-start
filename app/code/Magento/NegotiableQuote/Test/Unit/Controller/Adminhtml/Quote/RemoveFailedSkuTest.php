<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * Class RemoveFailedSkuTest
 */
class RemoveFailedSkuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\NegotiableQuote\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cart;

    /**
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\RemoveFailedSku
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactory;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRaw;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->cart = $this->createMock(\Magento\NegotiableQuote\Model\Cart::class);
        $this->resultRawFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RawFactory::class,
            ['create']
        );
        $this->resultRaw = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Raw::class,
            ['setContents', 'setHeader']
        );
        $this->resultRaw->expects($this->once())->method('setContents')->will($this->returnValue($this->resultRaw));
        $this->resultRaw->expects($this->once())->method('setHeader')->will($this->returnValue($this->resultRaw));
        $this->resultRawFactory->expects($this->once())->method('create')->will($this->returnValue($this->resultRaw));

        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            ['getParam'],
            '',
            false,
            false,
            true,
            []
        );
        $this->context = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest']
        );
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
    }

    /**
     * Creates an instance of subject under test
     */
    private function createInstance()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\RemoveFailedSku::class,
            [
                'context' => $this->context,
                'logger' => $this->logger,
                'messageManager' => $this->messageManager,
                'cart' => $this->cart,
                'resultRawFactory' => $this->resultRawFactory
            ]
        );
    }

    /**
     * Test for execute() method with exception
     */
    public function testExecuteWithException()
    {
        $this->cart->expects($this->once())
            ->method('removeFailedSku')
            ->willThrowException(new \Exception());
        $this->logger->expects($this->once())->method('critical');
        $this->messageManager->expects($this->once())->method('addError');
        $this->createInstance();
        $result = $this->controller->execute();

        $this->assertSame($result, $this->resultRaw);
    }

    /**
     * Test for execute() method
     */
    public function testExecute()
    {
        $sku = 'test_sku';
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->request->expects(($this->once()))
            ->method('getParam')
            ->with('remove_sku')
            ->willReturn($sku);
        $this->cart->expects($this->once())
            ->method('removeFailedSku')
            ->with($sku);
        $this->createInstance();
        $result = $this->controller->execute();

        $this->assertSame($result, $this->resultRaw);
    }
}
