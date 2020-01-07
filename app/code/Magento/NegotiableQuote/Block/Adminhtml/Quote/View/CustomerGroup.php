<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View;

/**
 * Add customer group information to quote.
 *
 * @api
 * @since 100.0.0
 */
class CustomerGroup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Magento\Customer\Api\Data\GroupInterface
     */
    private $customerGroup;

    /**
     * @var \Magento\NegotiableQuote\Model\PurgedContentFactory
     */
    private $purgedContentFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->companyManagement = $companyManagement;
        $this->groupRepository = $groupRepository;
        $this->purgedContentFactory = $purgedContentFactory;
        $this->serializer = $serializer;
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
    }

    /**
     * Get quote.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote()
    {
        return $this->negotiableQuoteHelper->resolveCurrentQuote();
    }

    /**
     * Retrieve customer group edit url.
     *
     * @return string
     */
    public function getGroupUrl()
    {
        $groupUrl = '';

        if ($this->getCustomerGroup() && $this->getCustomerGroup()->getId()) {
            $groupUrl = $this->getUrl(
                'customer/group/edit',
                [\Magento\Customer\Api\Data\GroupInterface::ID => $this->getCustomerGroup()->getId()]
            );
        }

        return $groupUrl;
    }

    /**
     * Retrieve customer group name.
     *
     * @return string
     */
    public function getGroupName()
    {
        $groupName = '';

        if ($this->getCustomerGroup() && $this->getCustomerGroup()->getCode()) {
            $groupName = $this->getCustomerGroup()->getCode();
        }

        return $groupName;
    }

    /**
     * Retrieve customer group.
     *
     * @return \Magento\Customer\Api\Data\GroupInterface|null
     */
    private function getCustomerGroup()
    {
        if ($this->customerGroup === null) {
            try {
                $customerGroupId = $this->getCustomerGroupId();
                $this->customerGroup = $this->groupRepository->getById($customerGroupId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->customerGroup = null;
            }
        }

        return $this->customerGroup;
    }

    /**
     * Get company for negotiable quote.
     *
     * @return \Magento\Company\Api\Data\CompanyInterface|null
     */
    private function getCompany()
    {
        if (!$this->company) {
            if ($this->getQuote() && $this->getQuote()->getCustomer()->getId()) {
                $customerId = $this->getQuote()->getCustomer()->getId();
                try {
                    $this->company = $this->companyManagement->getByCustomerId($customerId);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->company = null;
                }
            }
        }

        return $this->company;
    }

    /**
     * Retrieves customer group id from company or from purged data in case user company was deleted.
     *
     * @return int|null
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = null;
        if ($this->getCompany() !== null) {
            $customerGroupId = $this->getCompany()->getCustomerGroupId();
        } else {
            $purgedContent = $this->purgedContentFactory->create()->load($this->getQuote()->getId());
            $parsedContent = $this->serializer->unserialize($purgedContent->getPurgedData());
            $customerGroupId = (!empty($parsedContent['customer_group_id']))
                ? $parsedContent['customer_group_id'] : null;
        }

        return $customerGroupId;
    }
}
