<?php

namespace Magento\NegotiableQuote\Plugin\Sales\Helper\Reorder;

use Magento\Company\Model\CompanyContext;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\Order;

/**
 * Plugin for allowing reorder of specific products.
 */
class AllowSpecificProductsPlugin
{
    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param CompanyContext $companyContext
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CompanyContext $companyContext,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->companyContext = $companyContext;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Add reorder limitations for company users.
     *
     * @param Reorder $subject
     * @param \Closure $proceed
     * @param int $orderId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCanReorder(
        Reorder $subject,
        \Closure $proceed,
        $orderId
    ) {
        if (!$this->companyContext->isCurrentUserCompanyUser()) {
            return $proceed($orderId);
        }

        $order = $this->orderRepository->get($orderId);

        return $this->isReorderAvailable($order);
    }

    /**
     * Is reorder available for order.
     *
     * @param Order $order
     * @return bool
     */
    private function isReorderAvailable(Order $order)
    {
        if ($order->canUnhold() || $order->isPaymentReview()) {
            return false;
        }

        if ($order->getActionFlag(Order::ACTION_FLAG_REORDER) === false) {
            return false;
        }

        return true;
    }
}
