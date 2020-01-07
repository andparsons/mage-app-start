<?php

namespace Magento\NegotiableQuote\Controller\Order;

/**
 * Class OrderViewAuthorization
 */
class OrderViewAuthorization implements \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
{
    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structure;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @param \Magento\Company\Model\Company\Structure $structure
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        \Magento\Company\Model\Company\Structure $structure,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->structure = $structure;
        $this->orderConfig = $orderConfig;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function canView(\Magento\Sales\Model\Order $order)
    {
        $customerId = $this->userContext->getUserId();
        $availableStatuses = $this->orderConfig->getVisibleOnFrontStatuses();
        $allowedChildIds = $this->structure->getAllowedChildrenIds($customerId);
        $allowedChildIds[] = $customerId;

        if ($order->getId()
            && $order->getCustomerId()
            && in_array($order->getCustomerId(), $allowedChildIds)
            && in_array($order->getStatus(), $availableStatuses, true)
        ) {
            return true;
        }
        return false;
    }
}
