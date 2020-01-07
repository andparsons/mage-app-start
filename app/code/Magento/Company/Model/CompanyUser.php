<?php

namespace Magento\Company\Model;

/**
 * Class for getting company id from current company user.
 */
class CompanyUser
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->customerRepository = $customerRepository;
        $this->userContext = $userContext;
    }

    /**
     * Get current company id.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentCompanyId()
    {
        $customer = $this->customerRepository->getById($this->userContext->getUserId());
        return $customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getCompanyAttributes()
            ? $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
            : null;
    }
}
