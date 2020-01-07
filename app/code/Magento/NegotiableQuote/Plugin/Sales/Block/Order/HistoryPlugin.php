<?php

namespace Magento\NegotiableQuote\Plugin\Sales\Block\Order;

/**
 * Class HistoryPlugin
 */
class HistoryPlugin
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Block\Order\OwnerFilter
     */
    private $ownerFilterBlock;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * HistoryPlugin constructor.
     *
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\NegotiableQuote\Block\Order\OwnerFilter $ownerFilterBlock
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\NegotiableQuote\Block\Order\OwnerFilter $ownerFilterBlock,
        \Magento\Company\Api\AuthorizationInterface $authorization
    ) {
        $this->userContext = $userContext;
        $this->request = $request;
        $this->ownerFilterBlock = $ownerFilterBlock;
        $this->authorization = $authorization;
    }

    /**
     * After history getOrders plugin
     *
     * @param \Magento\Sales\Block\Order\History $subject
     * @param bool|\Magento\Sales\Model\ResourceModel\Order\Collection $result
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOrders(
        \Magento\Sales\Block\Order\History $subject,
        $result
    ) {
        $createdBy = $this->request->getParam('created_by');

        if (($result !== false) &&
            ($createdBy === $this->ownerFilterBlock->getShowMyParam() ||
                !$this->authorization->isAllowed('Magento_Sales::view_orders_sub')
            )
        ) {
            $customerId = $this->getCustomerId();
            $result->addFieldToFilter('customer_id', (int)$customerId);
        }

        return $result;
    }

    /**
     * Get customer id from user context
     *
     * @return int|null
     */
    private function getCustomerId()
    {
        return $this->userContext->getUserId() ? : null;
    }
}
