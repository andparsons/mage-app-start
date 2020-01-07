<?php

namespace Magento\Company\Model;

/**
 * Management class for role entity.
 */
class RoleManagement implements \Magento\Company\Api\RoleManagementInterface
{
    /**
     * @var int
     */
    private $companyAdminRoleId = 0;

    /**
     * @var string
     */
    private $companyAdminRoleName = 'Company Administrator';

    /**
     * @var string
     */
    private $companyDefaultRoleName = 'Default User';

    /**
     * @var \Magento\Company\Model\ResourceModel\Role\CollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * @var \Magento\Company\Model\RoleFactory
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Api\Data\RoleInterface
     */
    private $companyAdminRole;

    /**
     * @param \Magento\Company\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory
     * @param \Magento\Company\Model\RoleFactory $roleFactory
     */
    public function __construct(
        \Magento\Company\Model\ResourceModel\Role\CollectionFactory $roleCollectionFactory,
        \Magento\Company\Model\RoleFactory $roleFactory
    ) {
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->roleFactory = $roleFactory;
    }

    /**
     * Get company admin role ID.
     *
     * @return int
     */
    public function getCompanyAdminRoleId()
    {
        return $this->companyAdminRoleId;
    }

    /**
     * Get company admin role name.
     *
     * @return string
     */
    public function getCompanyAdminRoleName()
    {
        return $this->companyAdminRoleName;
    }

    /**
     * Get company default role name.
     *
     * @return string
     */
    public function getCompanyDefaultRoleName()
    {
        return $this->companyDefaultRoleName;
    }

    /**
     * @inheritdoc
     */
    public function getRolesByCompanyId($companyId, $includeAdminRole = true)
    {
        $roleCollection = $this->roleCollectionFactory->create();
        $roleCollection->addFieldToFilter('company_id', ['eq' => $companyId])
            ->setOrder('role_id', 'ASC')
            ->load();
        $roles = $roleCollection->getItems();
        if ($includeAdminRole === true) {
            $roles[] = $this->getAdminRole();
        }

        return $roles;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyDefaultRole($companyId)
    {
        $roles = $this->getRolesByCompanyId($companyId, false);

        return reset($roles);
    }

    /**
     * @inheritdoc
     */
    public function getAdminRole()
    {
        if ($this->companyAdminRole === null) {
            $this->companyAdminRole = $this->roleFactory->create();
            $this->companyAdminRole->setId($this->getCompanyAdminRoleId());
            $roleName = __($this->getCompanyAdminRoleName());
            $this->companyAdminRole->setRoleName($roleName);
        }

        return $this->companyAdminRole;
    }
}
