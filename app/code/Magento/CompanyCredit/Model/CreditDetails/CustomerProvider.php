<?php

namespace Magento\CompanyCredit\Model\CreditDetails;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Class CustomerProvider.
 */
class CustomerProvider
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * AdminProvider constructor.
     *
     * @param \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     */
    public function __construct(
        \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement
    ) {
        $this->creditDataProvider = $creditDataProvider;
        $this->userContext = $userContext;
        $this->companyManagement = $companyManagement;
    }

    /**
     * Get current user credit.
     *
     * @return \Magento\CompanyCredit\Api\Data\CreditDataInterface|null
     */
    public function getCurrentUserCredit()
    {
        $credit = null;

        if ($this->userContext->getUserId()
            && $this->userContext->getUserType()
            === \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER
        ) {
            $company = $this->companyManagement->getByCustomerId($this->userContext->getUserId());

            if ($company) {
                $credit = $this->creditDataProvider->get($company->getId());
            }
        }

        return $credit;
    }
}
