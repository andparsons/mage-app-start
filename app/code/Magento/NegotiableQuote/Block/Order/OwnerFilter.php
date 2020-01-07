<?php

namespace Magento\NegotiableQuote\Block\Order;

use Magento\Framework\View\Element\Template;

/**
 * Show my orders/Show all orders filter class.
 *
 * @api
 * @since 100.0.0
 */
class OwnerFilter extends Template
{
    /**
     * Show all quotes
     *
     * @var string
     */
    private $showMy = 'me';

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $customerContext;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structure;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\Company\Api\StatusServiceInterface
     */
    private $companyModuleConfig;

    /**
     * OwnerFilter constructor.
     *
     * @param Template\Context $context
     * @param \Magento\Authorization\Model\UserContextInterface $customerContext
     * @param \Magento\Company\Model\Company\Structure $structure
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\Company\Api\StatusServiceInterface $companyModuleConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Authorization\Model\UserContextInterface $customerContext,
        \Magento\Company\Model\Company\Structure $structure,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\Company\Api\StatusServiceInterface $companyModuleConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerContext = $customerContext;
        $this->structure = $structure;
        $this->authorization = $authorization;
        $this->companyModuleConfig = $companyModuleConfig;
    }

    /**
     * Getter for showMy parameter.
     *
     * @return string
     */
    public function getShowMyParam()
    {
        return $this->showMy;
    }

    /**
     * All url.
     *
     * @return string
     */
    public function getAllUrl()
    {
        return $this->getUrl(
            '*/*/*'
        );
    }

    /**
     * Owner url  url.
     *
     * @return string
     */
    public function getOwnerUrl()
    {
        return $this->getUrl(
            '*/*/*',
            ['_query' => 'created_by=' . $this->getShowMyParam()]
        );
    }

    /**
     * Check link for showing all orders.
     *
     * @return bool
     */
    public function isViewAll()
    {
        $showAll = false;
        $createdBy = $this->getRequest()->getParam('created_by');
        if ($createdBy !== $this->getShowMyParam()) {
            $showAll = true;
        }
        return $showAll;
    }

    /**
     * Can we show the block or not.
     *
     * @return bool
     */
    public function canShow()
    {
        $customerId = $this->customerContext->getUserId();

        if ($this->companyModuleConfig->isActive()) {
            $subCustomers = $this->structure->getAllowedChildrenIds($customerId);
        }

        if (empty($subCustomers)
            || !$this->authorization->isAllowed('Magento_Sales::view_orders')
            || !$this->authorization->isAllowed('Magento_Sales::view_orders_sub')
        ) {
            return false;
        }

        return true;
    }
}
