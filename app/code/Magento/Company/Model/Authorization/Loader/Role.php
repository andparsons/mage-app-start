<?php
namespace Magento\Company\Model\Authorization\Loader;

/**
 * Access Control List loader.
 */
class Role implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Role\Collection
     */
    private $collection;

    /**
     * @var \Magento\Authorization\Model\Acl\Role\UserFactory
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Model\CompanyUser
     */
    private $companyUser;

    /**
     * @param \Magento\Authorization\Model\Acl\Role\UserFactory $roleFactory
     * @param \Magento\Company\Model\ResourceModel\Role\Collection $collection
     * @param \Magento\Company\Model\CompanyUser $companyUser
     */
    public function __construct(
        \Magento\Authorization\Model\Acl\Role\UserFactory $roleFactory,
        \Magento\Company\Model\ResourceModel\Role\Collection $collection,
        \Magento\Company\Model\CompanyUser $companyUser
    ) {
        $this->roleFactory = $roleFactory;
        $this->collection = $collection;
        $this->companyUser = $companyUser;
    }

    /**
     * Populate ACL with roles from external storage.
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     */
    public function populateAcl(\Magento\Framework\Acl $acl)
    {
        $companyId = $this->companyUser->getCurrentCompanyId();
        if ($companyId) {
            $this->collection->addFieldToFilter(\Magento\Company\Api\Data\RoleInterface::COMPANY_ID, $companyId);
            $roles = $this->collection->getItems();
            /** @var \Magento\Company\Api\Data\RoleInterface $role */
            foreach ($roles as $role) {
                $acl->addRole($this->roleFactory->create(['roleId' => $role->getId()]));
            }
        }
    }
}
