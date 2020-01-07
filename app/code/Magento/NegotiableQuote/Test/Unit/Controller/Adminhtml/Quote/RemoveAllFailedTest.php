<?php
namespace Magento\NegotiableQuote\Test\Unit\Controller\Adminhtml\Quote;

/**
 * Class RemoveAllFailedTest
 */
class RemoveAllFailedTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\NegotiableQuote\Controller\Adminhtml\Quote\RemoveAllFailed
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactory;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->cart = $this->createMock(\Magento\NegotiableQuote\Model\Cart::class);
        $this->resultRawFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\RawFactory::class, ['create']);
        $resultRaw =
            $this->createPartialMock(\Magento\Framework\Controller\Result\Raw::class, ['setContents', 'setHeader']);
        $resultRaw->expects($this->once())->method('setContents')->will($this->returnValue($resultRaw));
        $resultRaw->expects($this->once())->method('setHeader')->will($this->returnValue($resultRaw));
        $this->resultRawFactory->expects($this->once())->method('create')->will($this->returnValue($resultRaw));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Adminhtml\Quote\RemoveAllFailed::class,
            [
                'logger' => $this->logger,
                'messageManager' => $this->messageManager,
                'cart' => $this->cart,
                'resultRawFactory' => $this->resultRawFactory
            ]
        );
    }

    /**
     * Test for execute() method
     */
    public function testExecute()
    {
        $this->cart->expects($this->once())
            ->method('removeAllFailed');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Raw::class, $this->controller->execute());
    }

    /**
     * Test for execute() method with exception
     */
    public function testExecuteWithException()
    {
        $this->cart->expects($this->once())
            ->method('removeAllFailed')
            ->willThrowException(new \Exception());
        $this->logger->expects($this->once())->method('critical');
        $this->messageManager->expects($this->once())->method('addError');

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Raw::class, $this->controller->execute());
    }
}
