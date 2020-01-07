<?php
namespace Magento\SharedCatalog\Model;

/**
 * Shared Catalog validator.
 */
class SharedCatalogValidator
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory
     */
    private $sharedCatalogCollectionFactory;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory
     */
    private $taxClassCollectionFactory;

    /**
     * @var array
     */
    private $allowedCustomerTaxClasses;

    /**
     * @var bool
     */
    private $validateStore;

    /**
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
     * @param ResourceModel\SharedCatalog\CollectionFactory $sharedCatalogCollectionFactory
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory
     * @param bool $validateStore [optional]
     */
    public function __construct(
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement,
        \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory $sharedCatalogCollectionFactory,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory,
        $validateStore = false
    ) {
        $this->sharedCatalogManagement = $sharedCatalogManagement;
        $this->sharedCatalogCollectionFactory = $sharedCatalogCollectionFactory;
        $this->storeRepository = $storeRepository;
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->validateStore = $validateStore;
    }

    /**
     * Validate shared catalog.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @throws \Magento\Framework\Exception\InputException|\Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function validate(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        $this->getAllowedCustomerTaxClasses();

        if ($sharedCatalog->getId()) {
            $this->checkSharedCatalogExist($sharedCatalog);
        }

        $this->validateSharedCatalogData($sharedCatalog);
        $this->validateSharedCatalogType($sharedCatalog);
        $this->validateSharedCatalogTaxClass($sharedCatalog);

        if ($sharedCatalog->getStoreId()) {
            $this->storeRepository->getById($sharedCatalog->getStoreId());
        }

        $this->validateSharedCatalogName($sharedCatalog);
        $this->validateCustomerGroupChanges($sharedCatalog);
    }

    /**
     * Is public catalog duplicated.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return bool
     */
    public function isCatalogPublicTypeDuplicated(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        if ($sharedCatalog->getType() != \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC) {
            return false;
        }

        try {
            $publicCatalog = $this->sharedCatalogManagement->getPublicCatalog();
            $isPublicDuplicated = $sharedCatalog->getId() != $publicCatalog->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $isPublicDuplicated = false;
        }
        return $isPublicDuplicated;
    }

    /**
     * Is direct change public catalog to custom.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function isDirectChangeToCustom(
        \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
    ) {
        if ($sharedCatalog->getType() != \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM) {
            return false;
        }

        try {
            $publicCatalog = $this->sharedCatalogManagement->getPublicCatalog();
            $isDirectChangeToCustom = $sharedCatalog->getId() == $publicCatalog->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $isDirectChangeToCustom = false;
        }
        if ($isDirectChangeToCustom) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'You cannot convert a public catalog to a custom catalog. '
                    . 'If you need to replace this public catalog, '
                    . 'create a custom catalog, then change its type to public.'
                )
            );
        }

        return true;
    }

    /**
     * Check Shared Catalog exist.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function checkSharedCatalogExist(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        if (!$this->getOriginalSharedCatalog($sharedCatalog)->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested Shared Catalog is not found'));
        }
    }

    /**
     * Check type Shared Catalog.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @throws \Magento\Framework\Exception\LocalizedException|\Magento\Framework\Exception\NoSuchEntityException
     * @return bool
     */
    public function isSharedCatalogPublic(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        $this->checkSharedCatalogExist($sharedCatalog);
        if ($sharedCatalog->getType() == $sharedCatalog::TYPE_PUBLIC) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Catalog (id:%1) cannot be deleted because it is a public catalog. '
                    . 'You must create a new public catalog before you can delete this catalog.',
                    $sharedCatalog->getId()
                )
            );
        }
        return true;
    }

    /**
     * Get original SharedCatalog.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     */
    private function getOriginalSharedCatalog(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        /** @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection $collection */
        $collection = $this->sharedCatalogCollectionFactory->create();
        $collection->addFieldToFilter($sharedCatalog::SHARED_CATALOG_ID, ['eq' => $sharedCatalog->getId()]);
        /** @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog */
        $sharedCatalog = $collection->getFirstItem();

        return $sharedCatalog;
    }

    /**
     * Get allowed customer tax classes.
     *
     * @return array
     */
    private function getAllowedCustomerTaxClasses()
    {
        if ($this->allowedCustomerTaxClasses !== null) {
            return $this->allowedCustomerTaxClasses;
        }

        $taxClasses = $this->taxClassCollectionFactory->create()->getItems();

        foreach ($taxClasses as $taxClassItem) {
            $this->allowedCustomerTaxClasses[$taxClassItem->getId()] = $taxClassItem->getId();
        }

        return $this->allowedCustomerTaxClasses;
    }

    /**
     * Validate shared catalog required fields.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    private function validateSharedCatalogData(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        if (!$sharedCatalog->getId()
            && (empty($sharedCatalog->getName())
                || ($this->validateStore && $sharedCatalog->getStoreId() === null)
                || $sharedCatalog->getTaxClassId() === null)
        ) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'Cannot create a shared catalog because some information is missing. '
                    . 'Please make sure you provided Store Group ID, Name and Tax Class.'
                )
            );
        }
    }

    /**
     * Check is shared catalog type id exists.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function validateSharedCatalogType(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        if (!array_key_exists($sharedCatalog->getType(), $sharedCatalog->getAvailableTypes())) {
            throw \Magento\Framework\Exception\NoSuchEntityException::singleField(
                'type_id',
                $sharedCatalog->getType()
            );
        }
    }

    /**
     * Check is shared catalog tax class id exists.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function validateSharedCatalogTaxClass(
        \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
    ) {
        $allowedCustomerTaxClasses = $this->getAllowedCustomerTaxClasses();
        if (!isset($allowedCustomerTaxClasses[$sharedCatalog->getTaxClassId()])) {
            throw \Magento\Framework\Exception\NoSuchEntityException::singleField(
                'tax_class_id',
                $sharedCatalog->getTaxClassId()
            );
        }
    }

    /**
     * Check is shared catalog name duplicated.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    private function validateSharedCatalogName(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        if (mb_strlen($sharedCatalog->getName()) > \Magento\Customer\Api\Data\GroupInterface::GROUP_CODE_MAX_LENGTH) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'The maximum allowed catalog name length is %1 characters.',
                    \Magento\Customer\Api\Data\GroupInterface::GROUP_CODE_MAX_LENGTH
                )
            );
        }
        /** @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection $collection */
        $collection = $this->sharedCatalogCollectionFactory->create();
        $collection->addFieldToFilter($sharedCatalog::NAME, ['eq' => $sharedCatalog->getName()]);
        if (!empty($sharedCatalog->getId())) {
            $collection->addFieldToFilter($sharedCatalog::SHARED_CATALOG_ID, ['neq' => $sharedCatalog->getId()]);
        }
        /** @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $existSharedCatalog */
        $existSharedCatalog = $collection->getFirstItem();

        if ((bool)$existSharedCatalog->getId()) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'A catalog named %catalogName already exists. Please select a different name.',
                    ['catalogName' => $sharedCatalog->getName()]
                )
            );
        }
    }

    /**
     * Check is shared catalog customer group id changed.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    private function validateCustomerGroupChanges(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        $originalSharedCatalog = $this->getOriginalSharedCatalog($sharedCatalog);

        if ($originalSharedCatalog && $originalSharedCatalog->getCustomerGroupId()
            && $originalSharedCatalog->getCustomerGroupId() != $sharedCatalog->getCustomerGroupId()
        ) {
            throw new \Magento\Framework\Exception\InputException(
                __('You cannot change the customer group for a shared catalog.')
            );
        }
    }
}
