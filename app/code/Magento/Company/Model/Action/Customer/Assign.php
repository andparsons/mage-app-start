<?php
namespace Magento\Company\Model\Action\Customer;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class for assigning a role to customer.
 */
class Assign
{
    /**
     * @var \Magento\Company\Api\AclInterface
     */
    private $acl;

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @param \Magento\Company\Api\AclInterface $acl
     * @param \Magento\Company\Api\RoleRepositoryInterface $roleRepository
     */
    public function __construct(
        \Magento\Company\Api\AclInterface $acl,
        \Magento\Company\Api\RoleRepositoryInterface $roleRepository
    ) {
        $this->acl = $acl;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Assign role to customer.
     *
     * @param CustomerInterface $customer
     * @param int $roleId
     * @return CustomerInterface
     */
    public function assignCustomerRole(CustomerInterface $customer, $roleId)
    {
        $role = $this->roleRepository->get($roleId);
        $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();

        if ($role && $role->getCompanyId() == $companyId) {
            $this->acl->assignRoles($customer->getId(), [$role]);
        }

        return $customer;
    }
}
