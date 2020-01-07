<?php

namespace Magento\NegotiableQuote\Plugin\Sales\Helper\Reorder;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Model\CompanyContext;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Reorder;

/**
 * Plugin for adding reorder limitations for company users.
 */
class CompanyUserLimitationsPlugin
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param UserContextInterface $userContext
     * @param CompanyContext $companyContext
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        UserContextInterface $userContext,
        CompanyContext $companyContext,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->userContext = $userContext;
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
        $isOrderOwner = $this->userContext->getUserId() === $order->getCustomerId();

        return $isOrderOwner && $proceed($orderId);
    }
}
