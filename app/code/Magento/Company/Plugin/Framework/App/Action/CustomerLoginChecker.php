<?php

namespace Magento\Company\Plugin\Framework\App\Action;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Company\Model\Customer\PermissionInterface;

/**
 * Helper for customer activity
 */
class CustomerLoginChecker
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface
     */
    private $permission;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param PermissionInterface $permission
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository,
        PermissionInterface $permission
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->permission = $permission;
    }

    /**
     * Get current customer.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomer()
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

    /**
     * Check user rights to login
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isLoginAllowed()
    {
        $customer = $this->getCustomer();
        return $customer && !$this->permission->isLoginAllowed($customer);
    }
}
