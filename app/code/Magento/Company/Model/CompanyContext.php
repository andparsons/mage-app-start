<?php

namespace Magento\Company\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * CompanyContext pool.
 *
 * @api
 * @since 100.0.0
 */
class CompanyContext
{
    /**
     * @var \Magento\Company\Api\StatusServiceInterface
     */
    private $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var CompanyUserPermission
     */
    private $companyUserPermission;

    /**
     * @var int
     */
    private $customerGroupId;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * CompanyContext constructor.
     *
     * @param \Magento\Company\Api\StatusServiceInterface $moduleConfig
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\Company\Model\CompanyUserPermission $companyUserPermission
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Company\Api\StatusServiceInterface $moduleConfig,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\Company\Model\CompanyUserPermission $companyUserPermission,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->userContext = $userContext;
        $this->authorization = $authorization;
        $this->companyUserPermission = $companyUserPermission;
        $this->customerRepository = $customerRepository;
        $this->httpContext = $httpContext;
    }

    /**
     * Checks if module is active.
     *
     * @return bool
     */
    public function isModuleActive()
    {
        return $this->moduleConfig->isActive();
    }

    /**
     * Checks if company registration from the storefront is allowed.
     *
     * @return bool
     */
    public function isStorefrontRegistrationAllowed()
    {
        return $this->moduleConfig->isStorefrontRegistrationAllowed();
    }

    /**
     * Checks if customer is logged in.
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return (bool)$this->getCustomerId();
    }

    /**
     * Checks if resource is allowed.
     *
     * @param string $resource
     * @return bool
     */
    public function isResourceAllowed($resource)
    {
        return $this->authorization->isAllowed($resource);
    }

    /**
     * Returns customer id.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->userContext->getUserId();
    }

    /**
     * Is current user company user.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isCurrentUserCompanyUser()
    {
        return $this->isCustomerLoggedIn() && $this->companyUserPermission->isCurrentUserCompanyUser();
    }

    /**
     * Retrieves customer group of the user.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerGroupId()
    {
        if ($this->customerGroupId === null) {
            $customerId = $this->getCustomerId();
            if ($customerId) {
                try {
                    $customer = $this->customerRepository->getById($customerId);
                    $this->customerGroupId = $customer->getGroupId();
                } catch (NoSuchEntityException $e) {
                }
            } else {
                $this->customerGroupId = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
            }
            if ($this->customerGroupId === null) {
                $this->customerGroupId = \Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID;
            }
        }
        return $this->customerGroupId;
    }
}
