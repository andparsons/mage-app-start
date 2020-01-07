<?php

declare(strict_types=1);

namespace Magento\Company\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Enforces current company ID.
 */
class ParamOverriderCompanyId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepo
     */
    public function __construct(UserContextInterface $userContext, CustomerRepositoryInterface $customerRepo)
    {
        $this->userContext = $userContext;
        $this->customerRepo = $customerRepo;
    }

    /**
     * @inheritDoc
     */
    public function getOverriddenValue()
    {
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
            && $this->userContext->getUserId()
        ) {
            $customer = $this->customerRepo->getById($this->userContext->getUserId());
            if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getCompanyAttributes()) {
                /** @var CompanyCustomerInterface $companyAttributes */
                $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
                return $companyAttributes->getCompanyId();
            }
        }

        return null;
    }
}
