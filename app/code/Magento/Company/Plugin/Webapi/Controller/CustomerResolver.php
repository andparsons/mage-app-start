<?php

namespace Magento\Company\Plugin\Webapi\Controller;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class CustomerResolver
 */
class CustomerResolver
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get current customer.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomer()
    {
        if ($this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            return null;
        }

        try {
            $customer = $this->customerRepository->getById($this->userContext->getUserId());
        } catch (NoSuchEntityException $e) {
            $customer = null;
        }

        return $customer;
    }
}
