<?php
namespace Magento\Company\Plugin\Customer\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses as DataProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Plugin for customer DataProvider.
 */
class DataProviderPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Adds company data from extension attributes to customer data.
     *
     * @param DataProvider $subject
     * @param array|null $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        DataProvider $subject,
        $result
    ) {
        if (!is_array($result)) {
            return $result;
        }
        try {
            foreach ($result as $customerId => &$customerData) {
                $customer = $this->customerRepository->getById($customerId);
                if (!isset($customerData['customer']['extension_attributes'])) {
                    $customerData['customer']['extension_attributes'] = [];
                }
                $customerData['customer']['extension_attributes']['company_attributes']
                    = $this->getCompanyAttributesData($customer);
            }
        } catch (NoSuchEntityException $e) {
        }
        return $result;
    }

    /**
     * Gets company data from customer extension attributes.
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function getCompanyAttributesData(CustomerInterface $customer)
    {
        $companyAttributesData = [];

        if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getCompanyAttributes()) {
            $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
            $companyAttributesData['status'] = (string)$companyAttributes->getStatus();
            $companyAttributesData['company_id'] = (string)$companyAttributes->getCompanyId();
            $companyId = $companyAttributes->getCompanyId();
            try {
                $company = $this->companyRepository->get($companyId);
                $companyAttributesData['company_name'] = $company->getCompanyName();
                $companyAttributesData['company_id'] = $company->getId();
                $companyAttributesData['is_super_user'] = (int) ($company->getSuperUserId() == $customer->getId());
            } catch (NoSuchEntityException $e) {
                $companyAttributesData['company_name'] = '';
                $companyAttributesData['company_id'] = '';
                $companyAttributesData['is_super_user'] = 0;
            }
        }

        return $companyAttributesData;
    }
}
