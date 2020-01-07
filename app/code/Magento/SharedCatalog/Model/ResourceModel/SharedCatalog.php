<?php

namespace Magento\SharedCatalog\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\SharedCatalog\Model\CustomerGroupManagement;

/**
 * SharedCatalog page mysql resource.
 */
class SharedCatalog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Main table primary key field name.
     *
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var \Magento\SharedCatalog\Api\CompanyManagementInterface $companyManagement,
     */
    private $companyManagement;

    /**
     * @var \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @var CustomerGroupManagement $customerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shared_catalog', 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\SharedCatalog\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement
     * @param string|null $connectionName [optional]
     * @param CustomerGroupManagement|null $customerGroupManagement
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\SharedCatalog\Api\CompanyManagementInterface $companyManagement,
        \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement,
        $connectionName = null,
        CustomerGroupManagement $customerGroupManagement = null
    ) {
        $this->companyManagement = $companyManagement;
        $this->catalogPermissionManagement = $catalogPermissionManagement;
        $this->customerGroupManagement = $customerGroupManagement ?:
            ObjectManager::getInstance()->get(CustomerGroupManagement::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Perform actions before object delete.
     *
     * @param \Magento\SharedCatalog\Model\SharedCatalog $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeDelete($object);
        $this->companyManagement->unassignAllCompanies($object->getId());
        $this->catalogPermissionManagement->removeAllPermissions((int) $object->getCustomerGroupId());
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);

        $this->customerGroupManagement->deleteCustomerGroupById($object);
    }
}
