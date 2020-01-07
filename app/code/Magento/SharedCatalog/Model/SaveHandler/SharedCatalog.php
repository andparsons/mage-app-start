<?php

namespace Magento\SharedCatalog\Model\SaveHandler;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\SharedCatalog\Model\CustomerGroupManagement;
use Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save as SharedCatalogSave;
use Magento\SharedCatalog\Model\SharedCatalogValidator;
use Psr\Log\LoggerInterface;

/**
 * Handler for shared catalog save.
 *
 * Save shared catalog and update its bound entities (customer groups, category permissions, companies, etc.).
 */
class SharedCatalog
{
    /**
     * @var CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var SharedCatalogValidator
     */
    private $validator;

    /**
     * @var CatalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SharedCatalogSave
     */
    private $save;

    /**
     * @param CustomerGroupManagement $customerGroupManagement
     * @param CatalogPermissionManagement $catalogPermissionManagement
     * @param SharedCatalogValidator $validator
     * @param LoggerInterface $logger
     * @param SharedCatalogSave $save
     */
    public function __construct(
        CustomerGroupManagement $customerGroupManagement,
        CatalogPermissionManagement $catalogPermissionManagement,
        SharedCatalogValidator $validator,
        LoggerInterface $logger,
        SharedCatalogSave $save
    ) {
        $this->customerGroupManagement = $customerGroupManagement;
        $this->catalogPermissionManagement = $catalogPermissionManagement;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->save = $save;
    }

    /**
     * Shared Catalog saving.
     *
     * If it is a new shared catalog then customer group will be created.
     * If it is an existing shared catalog and the shared catalog name changes then related customer group name updated
     * will be updated.
     * If a shared catalog type is being changed to public then all companies from the current public shared catalog
     * to the new public shared catalog will be reassigned.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param SharedCatalogInterface $originalSharedCatalog
     * @return SharedCatalogInterface
     * @throws CouldNotSaveException
     * @throws \Exception
     */
    public function execute(
        SharedCatalogInterface $sharedCatalog,
        SharedCatalogInterface $originalSharedCatalog
    ): SharedCatalogInterface {
        try {
            $this->validator->isDirectChangeToCustom($sharedCatalog);
            $this->save->prepare($sharedCatalog);
            $this->save->execute($sharedCatalog);
            $this->customerGroupManagement->updateCustomerGroup($sharedCatalog);
            if (!$originalSharedCatalog->getId()) {
                $this->catalogPermissionManagement->setDenyPermissionsForCustomerGroup(
                    $sharedCatalog->getCustomerGroupId()
                );
            }
        } catch (CouldNotSaveException $e) {
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            throw new CouldNotSaveException(__('Could not save shared catalog.'));
        }

        return $sharedCatalog;
    }
}
