<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Sales\Controller\Order;

use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;

/**
 * Unit test for Plugin/Sales/Controller/Order/ReorderPlugin model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cart;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderLoader;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Sales\Controller\Order\Reorder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Sales\Controller\Order\ReorderPlugin
     */
    private $reorderPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->cart = $this->getMockBuilder(\Magento\Checkout\Model\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderLoader = $this->getMockBuilder(OrderLoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepository = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->addressRepository = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subject = $this->getMockBuilder(\Magento\Sales\Controller\Order\Reorder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->reorderPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Sales\Controller\Order\ReorderPlugin::class,
            [
                'messageManager' => $this->messageManager,
                'resultFactory' => $this->resultFactory,
                'cart' => $this->cart,
                'orderLoader' => $this->orderLoader,
                'orderRepository' => $this->orderRepository,
                'addressRepository' => $this->addressRepository,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * Test aroundExecute.
     *
     * @return void
     */
    public function testAroundExecute()
    {
        $exceptionMessage = 'test';
        $itemSku = 'item_sku';
        $exception = new \Exception(__($exceptionMessage));
        $this->cart->expects($this->any())->method('addOrderItem')->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage')
            ->with(__('Product with SKU %1 not found in catalog', $itemSku));
        $this->logger->expects($this->once())->method('critical')->with($exception);

        $this->aroundExecute();

        $item = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku'])
            ->getMock();
        $item->expects($this->atLeastOnce())->method('getSku')->willReturn('item_sku');
        $this->order->expects($this->atLeastOnce())->method('getItemsCollection')->willReturn([$item]);

        $proceed = function () {
            return true;
        };

        $this->assertInstanceOf(
            \Magento\Framework\Controller\ResultInterface::class,
            $this->reorderPlugin->aroundExecute($this->subject, $proceed)
        );
    }

    /**
     * Test aroundExecute with LocalizedException.
     *
     * @return void
     */
    public function testAroundExecuteWithLocalizedException()
    {
        $exceptionMessage = 'test';
        $exception = new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));
        $this->cart->expects($this->any())->method('addOrderItem')->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with($exceptionMessage);

        $this->aroundExecute();

        $item = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order->expects($this->atLeastOnce())->method('getItemsCollection')->willReturn([$item]);

        $proceed = function () {
            return true;
        };

        $this->assertInstanceOf(
            \Magento\Framework\Controller\ResultInterface::class,
            $this->reorderPlugin->aroundExecute($this->subject, $proceed)
        );
    }

    /**
     * Body for aroundExecute tests.
     *
     * @return void
     */
    private function aroundExecute()
    {
        $orderId = 1;

        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects($this->atLeastOnce())->method('getParam')->willReturn($orderId);
        $this->subject->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);
        $this->orderLoader->expects($this->atLeastOnce())->method('load')->willReturn(null);
        $quoteAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteAddress->expects($this->atLeastOnce())->method('importCustomerAddressData')->willReturnSelf();
        $quoteAddress->expects($this->atLeastOnce())->method('save')->willThrowException(
            new \Magento\Framework\Exception\NoSuchEntityException()
        );
        $orderAddress = $this->getMockBuilder(\Magento\Sales\Model\Order\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shippingMethod = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quotePayment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quotePayment->expects($this->atLeastOnce())->method('setMethod')->willReturnSelf();
        $quotePayment->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $orderPayment = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderPayment->expects($this->atLeastOnce())->method('getMethod')->willReturn('payment_method');

        $this->order->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($orderAddress);
        $this->order->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($orderAddress);
        $this->order->expects($this->atLeastOnce())->method('getShippingMethod')->willReturn($shippingMethod);
        $this->order->expects($this->atLeastOnce())->method('getPayment')->willReturn($orderPayment);
        $this->orderRepository->expects($this->any())->method('get')->willReturn($this->order);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($quoteAddress);
        $quote->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($quoteAddress);
        $quote->expects($this->atLeastOnce())->method('getPayment')->willReturn($quotePayment);
        $this->cart->expects($this->atLeastOnce())->method('getQuote')->willReturn($quote);
        $addressData = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->addressRepository->expects($this->atLeastOnce())->method('getById')->willReturn($addressData);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
    }

    /**
     * Test aroundExecute with result.
     *
     * @return void
     */
    public function testAroundExecuteWithResult()
    {
        $orderId = 1;
        $result = $this->createMock(\Magento\Framework\Controller\ResultInterface::class);
        $subject = $this->createMock(\Magento\Sales\Controller\Order\Reorder::class);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $request->expects($this->any())->method('getParam')->willReturn($orderId);
        $subject->expects($this->any())->method('getRequest')->willReturn($request);
        $this->orderLoader->expects($this->any())->method('load')->willReturn($result);
        $proceed = function () {
            return true;
        };

        $this->assertInstanceOf(
            \Magento\Framework\Controller\ResultInterface::class,
            $this->reorderPlugin->aroundExecute($subject, $proceed)
        );
    }
}
