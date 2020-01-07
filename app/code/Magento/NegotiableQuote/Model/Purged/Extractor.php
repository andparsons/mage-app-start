<?php

namespace Magento\NegotiableQuote\Model\Purged;

/**
 * Extracts data that should be stored in quote after deleting of related entities.
 */
class Extractor
{
    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerNameGenerator;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * Extractor constructor.
     *
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGenerator
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     */
    public function __construct(
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerNameGenerator,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement
    ) {
        $this->customerNameGenerator = $customerNameGenerator;
        $this->companyRepository = $companyRepository;
        $this->companyManagement = $companyManagement;
    }

    /**
     * Extract data that should be stored in quote after user removal and return all necessary values as array.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $user
     * @return array
     */
    public function extractCustomer(\Magento\Customer\Api\Data\CustomerInterface $user)
    {
        $data = [];

        $data['customer_name'] = $this->customerNameGenerator->getCustomerName($user);
        $companyId = $user->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
        $data[\Magento\Company\Api\Data\CompanyInterface::COMPANY_ID]
            = $user->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
        $company = $this->companyRepository->get($companyId);
        $companyAdmin = $this->companyManagement->getAdminByCompanyId($companyId);
        $data[\Magento\Company\Api\Data\CompanyInterface::NAME] = $company->getCompanyName();
        $data[\Magento\Company\Api\Data\CompanyInterface::EMAIL] = $companyAdmin->getEmail();
        $data[\Magento\Company\Api\Data\CompanyInterface::SALES_REPRESENTATIVE_ID]
            = $company->getSalesRepresentativeId();
        $data[\Magento\Company\Api\Data\CompanyInterface::CUSTOMER_GROUP_ID] = $companyAdmin->getGroupId();

        if ($company->getSalesRepresentativeId()) {
            $salesRepName = $this->companyManagement->getSalesRepresentative($company->getSalesRepresentativeId());

            if ($salesRepName) {
                $data['sales_representative_name'] = $salesRepName;
            }
        }

        return $data;
    }

    /**
     * Extract data that should be stored in quote after user removal and return all necessary values as array.
     *
     * @param \Magento\User\Api\Data\UserInterface $user
     * @return array
     */
    public function extractUser(\Magento\User\Api\Data\UserInterface $user)
    {
        $data = [];

        if (!$user->hasFirstName()) {
            $user->load($user->getId());
        }

        $data['sales_representative_name'] = $user->getFirstname() . ' ' . $user->getLastname();

        return $data;
    }
}
