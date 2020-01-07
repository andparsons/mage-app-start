<?php
namespace Magento\Company\CustomerData;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Company\Api\CompanyManagementInterface;

/**
 * Company section
 */
class Company implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    protected $companyContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface
     */
    protected $permission;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Model\Customer\PermissionInterface $permission
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CompanyManagementInterface $companyManagement
     */
    public function __construct(
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Model\Customer\PermissionInterface $permission,
        \Magento\Framework\App\Http\Context $httpContext,
        CompanyManagementInterface $companyManagement
    ) {
        $this->companyContext = $companyContext;
        $this->customerRepository = $customerRepository;
        $this->permission = $permission;
        $this->httpContext = $httpContext;
        $this->companyManagement = $companyManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $customer = $this->getCustomer();
        if ($customer === null) {
            return [];
        }
        return [
            'is_checkout_allowed' => $this->permission->isCheckoutAllowed($customer),
            'is_company_blocked' => $this->permission->isCompanyBlocked($customer),
            'is_login_allowed' => $this->permission->isLoginAllowed($customer),
            'is_enabled' => (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH),
            'has_customer_company' => $this->hasCustomerCompany(),
            'is_storefront_registration_allowed' => $this->companyContext->isStorefrontRegistrationAllowed(),
        ];
    }

    /**
     * Get current customer.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomer()
    {
        try {
            $customer = $this->customerRepository->getById($this->companyContext->getCustomerId());
        } catch (NoSuchEntityException $e) {
            $customer = null;
        }

        return $customer;
    }

    /**
     * Has current customer company.
     *
     * @return bool
     */
    private function hasCustomerCompany()
    {
        $hasCompany = false;
        $customerId = $this->companyContext->getCustomerId();
        if ($customerId) {
            $company = $this->companyManagement->getByCustomerId($customerId);
            if ($company) {
                $hasCompany = true;
            }
        }

        return $hasCompany;
    }
}
