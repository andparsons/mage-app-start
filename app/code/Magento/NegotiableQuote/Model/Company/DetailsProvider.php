<?php

namespace Magento\NegotiableQuote\Model\Company;

/**
 * Class provides company details that can be rendered on quote info page.
 */
class DetailsProvider
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerNameGenerator;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider
     */
    private $provider;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company;

    /**
     * DetailsProvider constructor.
     *
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGenerator
     * @param \Magento\NegotiableQuote\Model\Purged\Provider $provider
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     */
    public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGenerator,
        \Magento\NegotiableQuote\Model\Purged\Provider $provider,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $this->companyManagement = $companyManagement;
        $this->customerNameGenerator = $customerNameGenerator;
        $this->quote = $quote;
        $this->provider = $provider;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Retrieve company of quote owner.
     *
     * @return \Magento\Company\Api\Data\CompanyInterface|null
     */
    public function getCompany()
    {
        if (!$this->company) {
            $customer = $this->quote->getCustomer();

            try {
                if ($customer->getId()) {
                    $this->company = $this->companyManagement->getByCustomerId($customer->getId());
                } else {
                    $companyId = $this->provider->getCompanyId($this->quote->getId());
                    $this->company = $this->companyRepository->get($companyId);
                }
            } catch (\Exception $e) {
                $this->company = null;
            }
        }

        return $this->company;
    }

    /**
     * Get sales representative name.
     *
     * @return string
     */
    public function getSalesRepresentativeName()
    {
        try {
            $customerId = $this->quote->getCustomer()->getId();

            if ($customerId && $this->companyManagement->getByCustomerId($customerId)) {
                $company = $this->companyManagement->getByCustomerId($customerId);
                $salesRepresentativeId = $company->getSalesRepresentativeId()
                    ? $company->getSalesRepresentativeId()
                    : $this->provider->getSalesRepresentativeId($this->quote->getId());
                $salesRepresentativeName = $this->companyManagement->getSalesRepresentative($salesRepresentativeId);
            } else {
                $salesRepresentativeId = $this->provider->getSalesRepresentativeId($this->quote->getId());
                $salesRepresentativeName = $this->companyManagement->getSalesRepresentative($salesRepresentativeId);
            }

            if (!$salesRepresentativeName) {
                $salesRepresentativeName = $this->provider->getSalesRepresentativeName($this->quote->getId());
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $salesRepresentativeName = $this->provider->getSalesRepresentativeName($this->quote->getId());
        }

        return $salesRepresentativeName;
    }

    /**
     * Get sales representative id.
     *
     * @return int
     */
    public function getSalesRepresentativeId()
    {
        try {
            $customerId = $this->quote->getCustomer()->getId();

            if ($customerId && $this->companyManagement->getByCustomerId($customerId)) {
                $company = $this->companyManagement->getByCustomerId($customerId);
                $salesRepresentativeId = $company->getSalesRepresentativeId()
                    ? $company->getSalesRepresentativeId()
                    : $this->provider->getSalesRepresentativeId($this->quote->getId());
            } else {
                $salesRepresentativeId = $this->provider->getSalesRepresentativeId($this->quote->getId());
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $salesRepresentativeId = $this->provider->getSalesRepresentativeId($this->quote->getId());
        }

        return $salesRepresentativeId;
    }

    /**
     * Checks if sales representative (admin user responsible for company) exists.
     *
     * @return bool
     */
    public function existsSalesRepresentative()
    {
        return (bool)$this->companyManagement->getSalesRepresentative($this->getSalesRepresentativeId());
    }

    /**
     * Get full name of quote owner.
     *
     * @return string
     */
    public function getQuoteOwnerName()
    {
        return $this->quote->getCustomer() && $this->quote->getCustomer()->getId()
            ? $this->customerNameGenerator->getCustomerName($this->quote->getCustomer())
            : $this->provider->getCustomerName($this->quote->getId());
    }

    /**
     * Get company admin email value.
     *
     * @return string
     */
    public function getCompanyAdminEmail()
    {
        if ($this->getCompanyAdmin() && !empty($this->getCompanyAdmin()['email'])) {
            $adminEmail = $this->getCompanyAdmin()['email'];
        } else {
            $adminEmail = $this->provider->getCompanyEmail($this->quote->getId());
        }

        return $adminEmail;
    }

    /**
     * Get company name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->getCompany() && $this->getCompany()->getCompanyName()
            ? $this->getCompany()->getCompanyName()
            : $this->provider->getCompanyName($this->quote->getId());
    }

    /**
     * Get company admin data.
     *
     * @return array
     */
    public function getCompanyAdmin()
    {
        $quoteOwnerCompanyId = $this->getCompany()
            ? $this->getCompany()->getId()
            : $this->provider->getCompanyId($this->quote->getId());

        $companyAdmin = $this->companyManagement->getAdminByCompanyId($quoteOwnerCompanyId);
        return $companyAdmin ? $companyAdmin->__toArray() : [];
    }
}
