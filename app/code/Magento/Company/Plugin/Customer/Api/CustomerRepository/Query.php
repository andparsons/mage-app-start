<?php
namespace Magento\Company\Plugin\Customer\Api\CustomerRepository;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory as CompanyCustomerExtension;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Company\Model\Customer\CompanyAttributes;

/**
 * A plugin for customer get operations for processing company routines.
 */
class Query
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterfaceFactory
     */
    private $companyCustomerAttributes;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Company\Model\Customer\CompanyAttributes
     */
    private $customerSaveAttributes;

    /**
     * CustomerRepository constructor.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param CompanyCustomerExtension $companyCustomerAttributes
     * @param DataObjectHelper $dataObjectHelper
     * @param CompanyAttributes $customerSaveAttributes
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        CompanyCustomerExtension $companyCustomerAttributes,
        DataObjectHelper $dataObjectHelper,
        CompanyAttributes $customerSaveAttributes
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->companyCustomerAttributes = $companyCustomerAttributes;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerSaveAttributes = $customerSaveAttributes;
    }

    /**
     * After get customer.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        return $this->getCustomer($customer);
    }

    /**
     * After get customer by ID.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        return $this->getCustomer($customer);
    }

    /**
     * Get customer.
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    private function getCustomer(CustomerInterface $customer)
    {
        if ($customer->getExtensionAttributes()
            && $customer->getExtensionAttributes()->getCompanyAttributes()
        ) {
            return $customer;
        }

        if (!$customer->getExtensionAttributes()) {
            $customerExtension = $this->extensionFactory->create(CustomerInterface::class);
            $customer->setExtensionAttributes($customerExtension);
        }

        $companyAttributes = $this->getCompanyAttributes($customer);

        if ($companyAttributes) {
            $customer->getExtensionAttributes()->setCompanyAttributes($companyAttributes);
        }

        return $customer;
    }

    /**
     * Get company attributes.
     *
     * @param CustomerInterface $customer
     * @return \Magento\Company\Api\Data\CompanyCustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCompanyAttributes(CustomerInterface $customer)
    {
        try {
            $companyAttributesArray = $this->customerSaveAttributes->getCompanyAttributes($customer);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong')
            );
        }
        if (!$companyAttributesArray) {
            return null;
        }
        $companyAttributes = $this->companyCustomerAttributes->create();
        $this->dataObjectHelper->populateWithArray(
            $companyAttributes,
            $companyAttributesArray,
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        return $companyAttributes;
    }
}
