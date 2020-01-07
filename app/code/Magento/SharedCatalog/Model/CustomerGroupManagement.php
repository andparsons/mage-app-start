<?php
namespace Magento\SharedCatalog\Model;

use Magento\Customer\Api\CustomerGroupConfigInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group as GroupResourceModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Management Customer Group for SharedCatalog.
 */
class CustomerGroupManagement
{
    /**
     * @var GroupResourceModel
     */
    private $groupResourceModel;

    /**
     * @var CustomerGroupConfigInterface
     */
    private $customerGroupConfig;

    /**
     * @var GroupInterfaceFactory
     */
    private $groupFactory;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param GroupResourceModel $groupResourceModel
     * @param CustomerGroupConfigInterface $customerGroupConfig
     * @param GroupInterfaceFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        GroupResourceModel $groupResourceModel,
        CustomerGroupConfigInterface $customerGroupConfig,
        GroupInterfaceFactory $groupFactory,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->groupResourceModel = $groupResourceModel;
        $this->customerGroupConfig = $customerGroupConfig;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Check if master catalog should be displayed for customer group.
     *
     * @param int $customerGroupId
     * @return bool
     */
    public function isMasterCatalogAvailable(int $customerGroupId): bool
    {
        return in_array($customerGroupId, $this->getGroupIdsNotInSharedCatalogs(), true);
    }

    /**
     * Get customer groups that are linked to shared catalog including guest customer group.
     *
     * @return int[]
     */
    public function getSharedCatalogGroupIds(): array
    {
        $connection = $this->groupResourceModel->getConnection();
        $select = $connection->select();
        $select->from(
            ['customer_group' => $this->groupResourceModel->getMainTable()],
            ['customer_group_id']
        );
        $select->joinLeft(
            ['shared_catalog' => $this->groupResourceModel->getTable('shared_catalog')],
            'customer_group.customer_group_id = shared_catalog.customer_group_id',
            []
        );
        $select->where(
            '(shared_catalog.entity_id IS NOT NULL OR customer_group.customer_group_id = ?)',
            GroupInterface::NOT_LOGGED_IN_ID
        );

        $values = [];
        foreach ($connection->fetchCol($select) as $value) {
            $values[] = (int) $value;
        }

        return $values;
    }

    /**
     * Get customer groups that are not linked to any shared catalog.
     *
     * @return int[]
     */
    public function getGroupIdsNotInSharedCatalogs(): array
    {
        $connection = $this->groupResourceModel->getConnection();
        $select = $connection->select();
        $select->from(
            ['customer_group' => $this->groupResourceModel->getMainTable()],
            ['customer_group_id']
        );
        $select->joinLeft(
            ['shared_catalog' => $this->groupResourceModel->getTable('shared_catalog')],
            'customer_group.customer_group_id = shared_catalog.customer_group_id',
            []
        );
        $select->where(
            '(shared_catalog.entity_id IS NULL AND customer_group.customer_group_id != ?)',
            GroupInterface::NOT_LOGGED_IN_ID
        );

        $values = [];
        foreach ($connection->fetchCol($select) as $value) {
            $values[] = (int) $value;
        }

        return $values;
    }

    /**
     * Set default customer group.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @throws LocalizedException
     * @return void
     */
    public function setDefaultCustomerGroup(SharedCatalogInterface $sharedCatalog)
    {
        try {
            $this->customerGroupConfig->setDefaultCustomerGroup($sharedCatalog->getCustomerGroupId());
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not set default customer group'));
        }
    }

    /**
     * Create customer group for SharedCatalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return GroupInterface
     * @throws CouldNotSaveException
     */
    public function createCustomerGroupForSharedCatalog(SharedCatalogInterface $sharedCatalog)
    {
        /** @var GroupInterface $customerGroup */
        $customerGroup = $this->groupFactory->create();
        $customerGroup->setCode($sharedCatalog->getName());
        if ($sharedCatalog->getTaxClassId()) {
            $customerGroup->setTaxClassId($sharedCatalog->getTaxClassId());
        }
        try {
            $customerGroup = $this->groupRepository->save($customerGroup);
        } catch (InvalidTransitionException $e) {
            throw new CouldNotSaveException(
                __('A customer group with this name already exists. Enter a different name to create a shared catalog.')
            );
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('Could not save customer group.'));
        }

        return $customerGroup;
    }

    /**
     * Delete customer group by ID.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If customer group cannot be deleted
     * @throws LocalizedException
     */
    public function deleteCustomerGroupById(SharedCatalogInterface $sharedCatalog)
    {
        return $this->groupRepository->deleteById($sharedCatalog->getCustomerGroupId());
    }

    /**
     * Update customer group code and tax class id.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer group ID is not found
     * @throws LocalizedException
     */
    public function updateCustomerGroup(SharedCatalogInterface $sharedCatalog)
    {
        $customerGroup = $this->groupRepository->getById($sharedCatalog->getCustomerGroupId());
        $changeCustomerGroupTaxClassIdResult = $this->changeTaxClassId($customerGroup, $sharedCatalog->getTaxClassId());
        $changeCustomerGroupCodeResult = $this->changeCustomerGroupCode($customerGroup, $sharedCatalog->getName());

        if ($changeCustomerGroupTaxClassIdResult || $changeCustomerGroupCodeResult) {
            try {
                $this->groupRepository->save($customerGroup);
                return true;
                // phpcs:ignore Magento2.Exceptions.ThrowCatch
            } catch (LocalizedException $e) {
                throw new LocalizedException(
                    __('Could not update shared catalog customer group')
                );
            }
        }

        return false;
    }

    /**
     * Set customer group tax class id if new tax class id differs from the initial one.
     *
     * @param GroupInterface $customerGroup
     * @param int $taxClassId
     * @return bool
     */
    private function changeTaxClassId(GroupInterface $customerGroup, $taxClassId)
    {
        if ($customerGroup && $customerGroup->getTaxClassId() != $taxClassId) {
            $customerGroup->setTaxClassId($taxClassId);
            return true;
        }

        return false;
    }

    /**
     * Set customer group code if new code differs from the initial one and customer group is Not Logged In.
     *
     * @param GroupInterface $customerGroup
     * @param string $customerGroupCode
     * @return bool
     */
    private function changeCustomerGroupCode(GroupInterface $customerGroup, $customerGroupCode)
    {
        if ($customerGroup && $customerGroup->getId() != GroupInterface::NOT_LOGGED_IN_ID
            && $customerGroup->getCode() != $customerGroupCode
        ) {
            $customerGroup->setCode($customerGroupCode);
            return true;
        }

        return false;
    }
}
