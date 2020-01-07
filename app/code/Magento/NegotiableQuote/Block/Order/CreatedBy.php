<?php

namespace Magento\NegotiableQuote\Block\Order;

use Magento\Framework\View\Element\Template;

/**
 * Class CreatedBy
 *
 * @api
 * @since 100.0.0
 */
class CreatedBy extends Template
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * CreatedBy constructor.
     *
     * @param Template\Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->customerViewHelper = $customerViewHelper;
    }

    /**
     * Set order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function getOrder()
    {
        return $this->order;
    }

    /**
     * Get customer name
     *
     * @return string
     */
    public function getCreatedBy()
    {
        $customerName = '';
        $customerId = $this->getOrder()->getCustomerId();

        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customerName = $this->customerViewHelper->getCustomerName($customer);
        }

        return $customerName;
    }
}
