<?php

namespace Magento\NegotiableQuote\Plugin\Sales\Controller\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class ReorderPlugin
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderPlugin
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderLoaderInterface
     */
    private $orderLoader;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Reorder constructor.
     *
     * @param Cart $cart
     * @param OrderLoaderInterface $orderLoader
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Cart $cart,
        OrderLoaderInterface $orderLoader,
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AddressRepositoryInterface $addressRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->cart = $cart;
        $this->orderLoader = $orderLoader;
        $this->messageManager = $context->getMessageManager();
        $this->resultFactory = $context->getResultFactory();
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Controller\Order\Reorder $subject
     * @param \Closure $proceed
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\Reorder $subject,
        \Closure $proceed
    ) {
        $orderId = $subject->getRequest()->getParam('order_id');
        $replaceCart = $subject->getRequest()->getParam('replace_cart');

        if ($orderId !== null && $replaceCart !== null) {
            $this->cart->truncate();
        }

        $result = $this->orderLoader->load($subject->getRequest());

        if ($result instanceof ResultInterface) {
            return $result;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        foreach ($order->getItemsCollection() as $item) {
            try {
                $this->cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager
                    ->addErrorMessage(__('Product with SKU %1 not found in catalog', $item->getSku()));
                $this->logger->critical($e);
            }
        }
        $this->cart->save();

        if ($order->getShippingAddress()) {
            $this->cart->getQuote()->getShippingAddress()->setShippingMethod($order->getShippingMethod());

            $this->orderAddressToQuoteAddress(
                $this->cart->getQuote()->getShippingAddress(),
                $order->getShippingAddress()
            );
        }

        $this->orderAddressToQuoteAddress(
            $this->cart->getQuote()->getBillingAddress(),
            $order->getBillingAddress()
        );

        $this->cart->getQuote()->getPayment()->setMethod($order->getPayment()->getMethod())->save();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('checkout/cart');
    }

    /**
     * Populates quote address based on order address info.
     *
     * @param AddressInterface $quoteAddress
     * @param OrderAddressInterface $orderAddress
     * @return void
     */
    private function orderAddressToQuoteAddress(AddressInterface $quoteAddress, OrderAddressInterface $orderAddress)
    {
        try {
            $addressData = $this->addressRepository->getById($orderAddress->getCustomerAddressId());
            $quoteAddress->importCustomerAddressData($addressData);
            $quoteAddress->save();
        } catch (NoSuchEntityException $e) {
            // If no such entity, skip
        }
    }
}
