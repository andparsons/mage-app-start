<?php
namespace Magento\Company\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Company\Model\ResourceModel\Customer;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Class for managing customer company extension attributes.
 */
class CompanyAttributes
{
    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface
     */
    private $companyAttributes;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterfaceFactory
     */
    private $companyCustomerAttributes;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var bool
     */
    private $needAssignCustomer = false;

    /**
     * @var bool|null
     */
    private $companyChange;

    /**
     * @var int
     */
    private $currentCustomerStatus;

    /**
     * @var AttributesSaver
     */
    private $attributesSaver;

    /**
     * @param Customer $customerResource
     * @param CompanyCustomerInterfaceFactory $companyCustomerAttributes
     * @param DataObjectHelper $dataObjectHelper
     * @param UserContextInterface $userContext
     * @param CompanyManagementInterface $companyManagement
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributesSaver $attributesSaver
     */
    public function __construct(
        Customer $customerResource,
        CompanyCustomerInterfaceFactory $companyCustomerAttributes,
        DataObjectHelper $dataObjectHelper,
        UserContextInterface $userContext,
        CompanyManagementInterface $companyManagement,
        ExtensionAttributesFactory $extensionFactory,
        AttributesSaver $attributesSaver
    ) {
        $this->customerResource = $customerResource;
        $this->companyCustomerAttributes = $companyCustomerAttributes;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->userContext = $userContext;
        $this->companyManagement = $companyManagement;
        $this->extensionFactory = $extensionFactory;
        $this->attributesSaver = $attributesSaver;
    }

    /**
     * Update customer company attributes.
     *
     * @param CustomerInterface $customer
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateCompanyAttributes(CustomerInterface $customer)
    {
        $this->companyAttributes = $this->getCompanyAttributesByCustomer($customer);
        $this->currentCustomerStatus = $this->getCustomerStatus($customer);
        $companyId = $this->getCompanyIdForCustomerSave($this->companyAttributes);
        $this->checkCompanyId($customer, $companyId);
        if ($this->currentCustomerStatus !== null && $this->companyAttributes->getStatus() === null) {
            $this->companyAttributes->setStatus($this->currentCustomerStatus);
        }
        $this->companyAttributes->setCompanyId($companyId);
        $this->companyAttributes->setCustomerId($customer->getId());
        if ($this->isCompanyChange(true) && $companyId) {
            $this->checkForCompanyAdmin($companyId, $customer);
            $this->needAssignCustomer = true;
        }
        return $this;
    }

    /**
     * Checks if company id is present for existing customer with existing company.
     *
     * @param CustomerInterface $customer
     * @param int $companyId
     * @return void
     * @throws CouldNotSaveException
     */
    private function checkCompanyId(CustomerInterface $customer, $companyId)
    {
        if ($customer->getId() && !$companyId && $this->companyManagement->getByCustomerId($customer->getId())) {
            throw new CouldNotSaveException(
                __(
                    'You cannot update the requested attribute. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => 'companyId', 'fieldValue' => $companyId]
                )
            );
        }
    }

    /**
     * Get company id.
     *
     * @return int|null
     */
    public function getCompanyId()
    {
        if ($this->companyAttributes) {
            return $this->companyAttributes->getCompanyId();
        }

        return null;
    }

    /**
     * Retrieves original customer status that was before any changes were made during the script run.
     *
     * @param CustomerInterface $customer
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerStatus(CustomerInterface $customer)
    {
        if ($customer->getId()) {
            return $this->getOriginalCompanyAttributes($customer->getId())->getStatus();
        }

        return null;
    }

    /**
     * Get company attribute for customer.
     *
     * @param CustomerInterface $customer
     * @return CompanyCustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyAttributesByCustomer(CustomerInterface $customer)
    {
        if ($customer->getExtensionAttributes() === null) {
            $customerExtension = $this->extensionFactory->create(CustomerInterface::class);
            $customer->setExtensionAttributes($customerExtension);
        }
        if ($customer->getExtensionAttributes()->getCompanyAttributes() === null) {
            $companyAttributes = $this->getOriginalCompanyAttributes($customer->getId());
            $customer->getExtensionAttributes()->setCompanyAttributes($companyAttributes);
        }
        return $customer->getExtensionAttributes()->getCompanyAttributes();
    }

    /**
     * Retrieves original attributes.
     *
     * @param int $customerId
     * @return CompanyCustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOriginalCompanyAttributes($customerId)
    {
        $companyAttributes = $this->companyCustomerAttributes->create();
        if ($customerId) {
            $companyAttributesArray = $this->customerResource->getCustomerExtensionAttributes($customerId);
            $this->dataObjectHelper->populateWithArray(
                $companyAttributes,
                $companyAttributesArray,
                CompanyCustomerInterface::class
            );
        }

        return $companyAttributes;
    }

    /**
     * Checks if a customer is a company admin of the company with given id.
     *
     * @param int $companyId
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    private function checkForCompanyAdmin($companyId, CustomerInterface $customer)
    {
        if ($companyId && $this->companyAttributes && $customer->getId()) {
            $company = $this->companyManagement->getByCustomerId($customer->getId());
            if ($company && $company->getSuperUserId() == $customer->getId()) {
                if ($this->companyAttributes->getCompanyId() != $company->getId()) {
                    throw new CouldNotSaveException(
                        __(
                            'Invalid attribute value. Cannot change company for a company admin.'
                        )
                    );
                }
                if (!$this->companyAttributes->getStatus()) {
                    throw new CouldNotSaveException(
                        __(
                            'The user %1 is the company admin and cannot be set to inactive. '
                            . 'You must set another user as the company admin first.',
                            $customer->getFirstname() . ' ' . $customer->getLastname()
                        )
                    );
                }
            }
        }
    }

    /**
     * Save attributes for company.
     *
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function saveCompanyAttributes(CustomerInterface $customer)
    {
        $isCompanyChange = $this->isCompanyChange();
        $companyId = $this->getCompanyIdForCustomerSave($this->companyAttributes);
        $this->attributesSaver->saveAttributes(
            $customer,
            $this->companyAttributes,
            $companyId,
            $isCompanyChange,
            $this->currentCustomerStatus
        );

        if ($this->needAssignCustomer && $isCompanyChange && $companyId > 0) {
            $this->companyManagement->assignCustomer($companyId, $customer->getId());
        }

        return $this;
    }

    /**
     * Checks if company change appeared.
     *
     * @param bool $isReset [optional]
     * @return bool|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCompanyChange($isReset = false)
    {
        if ($this->companyChange === null || $isReset) {
            $original = $this->getOriginalCompanyAttributes($this->companyAttributes->getCustomerId());
            $this->companyChange = $this->companyAttributes->getCompanyId() != $original->getCompanyId();
        }
        return $this->companyChange;
    }

    /**
     * Get company Id for customer from extensionAttributes or context.
     *
     * @param CompanyCustomerInterface $extensionAttributes
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCompanyIdForCustomerSave(
        CompanyCustomerInterface $extensionAttributes
    ) {
        if ($extensionAttributes->getCompanyId()) {
            return $extensionAttributes->getCompanyId();
        }

        $contextUserId = $this->userContext->getUserId();
        if ($contextUserId !== null && $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $contextUserData = $this->customerResource->getCustomerExtensionAttributes($contextUserId);
            if (isset($contextUserData['company_id'])) {
                return (int)$contextUserData['company_id'];
            }
        }
        return 0;
    }

    /**
     * Get company attributes.
     *
     * @param CustomerInterface $customer
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyAttributes(CustomerInterface $customer)
    {
        return $this->customerResource->getCustomerExtensionAttributes($customer->getId());
    }
}
