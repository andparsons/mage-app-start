<?php

namespace Magento\SharedCatalog\Model\SaveHandler\SharedCatalog;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\SharedCatalog\Model\CustomerGroupManagement;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog;

/**
 * Saver for shared catalog. Prepare shared catalog for save and save it to database.
 */
class Save
{
    /**
     * @var SharedCatalog
     */
    private $sharedCatalogResource;

    /**
     * @var CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param SharedCatalog $sharedCatalogResource
     * @param CustomerGroupManagement $customerGroupManagement
     * @param UserContextInterface $userContext
     */
    public function __construct(
        SharedCatalog $sharedCatalogResource,
        CustomerGroupManagement $customerGroupManagement,
        UserContextInterface $userContext
    ) {
        $this->sharedCatalogResource = $sharedCatalogResource;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->userContext = $userContext;
    }

    /**
     * Save shared catalog to database.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(SharedCatalogInterface $sharedCatalog)
    {
        $this->sharedCatalogResource->save($sharedCatalog);
    }

    /**
     * Prepare shared catalog data before save.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepare(SharedCatalogInterface $sharedCatalog)
    {
        if ($sharedCatalog->getCustomerGroupId()) {
            return;
        }

        $currentUserId = $this->userContext->getUserType() == UserContextInterface::USER_TYPE_ADMIN ?
            $this->userContext->getUserId() : null;
        $sharedCatalog->setCreatedBy($currentUserId);
        $customerGroup = $this->customerGroupManagement->createCustomerGroupForSharedCatalog($sharedCatalog);
        $sharedCatalog->setCustomerGroupId($customerGroup->getId());
        if ($sharedCatalog->getType() === null) {
            $sharedCatalog->setType(SharedCatalogInterface::TYPE_CUSTOM);
        }
    }
}
