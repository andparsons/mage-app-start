<?php

namespace Magento\SharedCatalog\Model\SaveHandler;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Handler for saving of duplicated public shared catalog. Save shared catalog and update its bound entities
 * (customer groups, category permissions, companies, etc.).
 */
class DuplicatedPublicSharedCatalog
{
    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface
     */
    private $sharedCatalogProductItemManagement;

    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var \Magento\SharedCatalog\Model\CatalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @var \Magento\SharedCatalog\Api\CompanyManagementInterface
     */
    private $sharedCatalogCompanyManagement;

    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface
     */
    private $categoryManagement;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save
     */
    private $save;

    /**
     * @param \Magento\SharedCatalog\Api\ProductItemManagementInterface $sharedCatalogProductItemManagement
     * @param \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement
     * @param \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement
     * @param \Magento\SharedCatalog\Api\CompanyManagementInterface $sharedCatalogCompanyManagement
     * @param \Magento\SharedCatalog\Api\CategoryManagementInterface $categoryManagement
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save $save
     */
    public function __construct(
        \Magento\SharedCatalog\Api\ProductItemManagementInterface $sharedCatalogProductItemManagement,
        \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement,
        \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement,
        \Magento\SharedCatalog\Api\CompanyManagementInterface $sharedCatalogCompanyManagement,
        \Magento\SharedCatalog\Api\CategoryManagementInterface $categoryManagement,
        \Psr\Log\LoggerInterface $logger,
        \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save $save
    ) {
        $this->sharedCatalogProductItemManagement = $sharedCatalogProductItemManagement;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->catalogPermissionManagement = $catalogPermissionManagement;
        $this->sharedCatalogCompanyManagement = $sharedCatalogCompanyManagement;
        $this->categoryManagement = $categoryManagement;
        $this->logger = $logger;
        $this->save = $save;
    }

    /**
     * Shared Catalog saving.
     *
     * Saving shared catalog if public shared catalog is duplicated.
     * If it is a new shared catalog, customer group will be created.
     * If it is an existing shared catalog and the shared catalog name is changing
     * then related customer group name will be updated.
     * Reassign all companies from the current public shared catalog to the new public shared catalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param SharedCatalogInterface $publicCatalog
     * @return SharedCatalogInterface
     * @throws CouldNotSaveException
     * @throws \Exception
     */
    public function execute(SharedCatalogInterface $sharedCatalog, SharedCatalogInterface $publicCatalog)
    {
        try {
            $publicCatalog->setType(SharedCatalogInterface::TYPE_CUSTOM);
            $this->sharedCatalogProductItemManagement->deletePricesForPublicCatalog();
            $this->save->execute($publicCatalog);
            $this->save->prepare($sharedCatalog);
            $this->save->execute($sharedCatalog);
            $this->customerGroupManagement->updateCustomerGroup($sharedCatalog);
            $this->customerGroupManagement->setDefaultCustomerGroup($sharedCatalog);
            $this->sharedCatalogProductItemManagement->addPricesForPublicCatalog();
            $this->sharedCatalogCompanyManagement->unassignAllCompanies($publicCatalog->getId());
            $this->catalogPermissionManagement->setDenyPermissions(
                $this->categoryManagement->getCategories($publicCatalog->getId()),
                [\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID]
            );
            $this->catalogPermissionManagement->setAllowPermissions(
                $this->categoryManagement->getCategories($sharedCatalog->getId()),
                [\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID]
            );
        } catch (CouldNotSaveException $e) {
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            throw new CouldNotSaveException(__('Could not save shared catalog.'));
        }
        return $sharedCatalog;
    }
}
