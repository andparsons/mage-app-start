<?php

namespace Magento\Company\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Company\Model\ResourceModel\Customer;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Company\Model\Company\Structure;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Model\Email\Sender;
use Magento\Company\Api\AclInterface;

/**
 * Save customer attributes for each company.
 */
class AttributesSaver
{
    /**
     * @var Customer
     */
    private $customerResource;

    /**
     * @var Structure
     */
    private $companyStructure;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var Sender
     */
    private $companyEmailSender;

    /**
     * @var AclInterface
     */
    private $userRoleManagement;

    /**
     * @param Customer $customerResource
     * @param Structure $companyStructure
     * @param CompanyManagementInterface $companyManagement
     * @param Sender $companyEmailSender
     * @param AclInterface $userRoleManagement
     */
    public function __construct(
        Customer $customerResource,
        Structure $companyStructure,
        CompanyManagementInterface $companyManagement,
        Sender $companyEmailSender,
        AclInterface $userRoleManagement
    ) {
        $this->customerResource = $customerResource;
        $this->companyStructure = $companyStructure;
        $this->companyManagement = $companyManagement;
        $this->companyEmailSender = $companyEmailSender;
        $this->userRoleManagement = $userRoleManagement;
    }

    /**
     * Save customer attributes for company.
     *
     * @param CustomerInterface $customer
     * @param CompanyCustomerInterface $companyAttributes
     * @param int $companyId
     * @param bool $isCompanyChange
     * @param int $currentCustomerStatus
     * @return void
     * @throws CouldNotSaveException
     */
    public function saveAttributes(
        CustomerInterface $customer,
        CompanyCustomerInterface $companyAttributes,
        $companyId,
        $isCompanyChange,
        $currentCustomerStatus
    ) {
        if ($companyAttributes && $customer->getId()) {
            $customer->getExtensionAttributes()->setCompanyAttributes($companyAttributes);
            $company = $this->companyManagement->getByCustomerId($customer->getId());
            $isSuperUser = $company ? $company->getSuperUserId() === $customer->getId()
                && $companyAttributes->getCompanyId() === $company->getId() : false;
            if ($company && (int)$companyAttributes->getStatus() === CompanyCustomerInterface::STATUS_INACTIVE) {
                if ($isSuperUser) {
                    throw new CouldNotSaveException(
                        __(
                            'The user %1 is the company admin and cannot be set to inactive. '
                            . 'You must set another user as the company admin first.',
                            $customer->getFirstname() . ' ' . $customer->getLastname()
                        )
                    );
                }
                $this->companyStructure->moveStructureChildrenToParent($customer->getId());
            }
            $companyAttributes->setCustomerId($customer->getId());
            $this->deleteRole($isSuperUser, $customer->getId());
            $this->customerResource->saveAdvancedCustomAttributes($companyAttributes);
            $this->updateCompanyStructure($customer, $companyId, $isCompanyChange);
            $this->sendNotification($customer, $companyAttributes, $currentCustomerStatus);
        }
    }

    /**
     * Updates company structure.
     *
     * @param CustomerInterface $customer
     * @param int $companyId
     * @param bool $isCompanyChange
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateCompanyStructure(CustomerInterface $customer, $companyId, $isCompanyChange)
    {
        if ($isCompanyChange && $companyId) {
            $this->companyStructure->moveStructureChildrenToParent($customer->getId());
            $this->companyStructure->removeCustomerNode($customer->getId());
            $companyAdmin = $this->companyManagement->getAdminByCompanyId($companyId);
            $companyAdminStructure = $this->companyStructure->getStructureByCustomerId($companyAdmin->getId());
            $this->companyStructure->addNode(
                $customer->getId(),
                0,
                $companyAdminStructure ? $companyAdminStructure->getId() : 0
            );
        }
    }

    /**
     * Send notification by sender.
     *
     * @param CustomerInterface $customer
     * @param CompanyCustomerInterface $companyAttributes
     * @param int $currentCustomerStatus
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function sendNotification(
        CustomerInterface $customer,
        CompanyCustomerInterface $companyAttributes,
        $currentCustomerStatus
    ) {
        if (isset($currentCustomerStatus) &&
            (int)$currentCustomerStatus !== (int)$companyAttributes->getStatus()
        ) {
            $this->companyEmailSender->sendUserStatusChangeNotificationEmail(
                $customer,
                $companyAttributes->getStatus()
            );
        }
    }

    /**
     * Delete user roles.
     *
     * @param bool $isSuperUser
     * @param int $customerId
     * @return void
     */
    private function deleteRole($isSuperUser, $customerId)
    {
        if ($isSuperUser) {
            $this->userRoleManagement->deleteRoles($customerId);
        }
    }
}
