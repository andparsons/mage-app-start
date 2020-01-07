<?php

namespace Magento\SharedCatalog\Setup\Patch\Data;

use Magento\SharedCatalog\Model\SharedCatalogFactory;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class InitializeSharedCatalog
 * @package Magento\SharedCatalog\Setup\Patch\Data
 */
class InitializeSharedCatalog implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogFactory
     */
    private $catalogFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Repository
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    private $taxClassRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @var string
     */
    private $defaultCustomerGroupCode = 'General';

    /**
     * @var int
     */
    private $defaultUserId = 1;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param SharedCatalogFactory $catalogFactory
     * @param \Magento\SharedCatalog\Model\Repository $sharedCatalogRepository
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UserCollectionFactory $userCollectionFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        SharedCatalogFactory $catalogFactory,
        \Magento\SharedCatalog\Model\Repository $sharedCatalogRepository,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        UserCollectionFactory $userCollectionFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->catalogFactory = $catalogFactory;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->groupManagement = $groupManagement;
        $this->groupRepository = $groupRepository;
        $this->taxClassRepository = $taxClassRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /**
         * @var $sharedCatalog SharedCatalogInterface
         */
        $sharedCatalog = $this->catalogFactory->create();
        $customerGroupId = $this->groupManagement->getDefaultGroup()->getId();
        $sharedCatalog->setName('Default (General)')
            ->setDescription('Default shared catalog')
            ->setCreatedBy($this->getDefaultUserId())
            ->setType(SharedCatalogInterface::TYPE_PUBLIC)
            ->setCustomerGroupId($customerGroupId)
            ->setTaxClassId($this->getRetailCustomerTaxClassId());

        $this->sharedCatalogRepository->save($sharedCatalog);
        $this->updateCustomerGroupCode($customerGroupId, $this->defaultCustomerGroupCode);
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get id of retail customer tax class.
     *
     * @return int|null
     */
    private function getRetailCustomerTaxClassId()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->create();
        $customerTaxClasses = $this->taxClassRepository->getList($searchCriteria)->getItems();
        $customerTaxClass = array_shift($customerTaxClasses);

        return ($customerTaxClass && $customerTaxClass->getClassId()) ? $customerTaxClass->getClassId() : null;
    }

    /**
     * Change code of default customer group.
     *
     * @param int $customerGroupId
     * @param string $customerGroupCode
     * @return \Magento\Customer\Api\Data\GroupInterface
     */
    private function updateCustomerGroupCode($customerGroupId, $customerGroupCode)
    {
        $customerGroup = $this->groupRepository->getById($customerGroupId);
        $customerGroup->setCode($customerGroupCode);

        return $this->groupRepository->save($customerGroup);
    }

    /**
     * Get default user id.
     *
     * @return int
     */
    private function getDefaultUserId()
    {
        /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
        $userCollection = $this->userCollectionFactory->create();
        /** @var UserInterface $user */
        $user = $userCollection->setPageSize(1)->getFirstItem();

        return $user->getId() ?: $this->defaultUserId;
    }
}
