<?php

namespace Magento\SharedCatalog\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Handler for shared catalog save. Save shared catalog and update its bound entities
 * (customer groups, category permissions, companies, etc.).
 */
class SaveHandler
{
    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogValidator
     */
    private $validator;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler\DuplicatedPublicSharedCatalog
     */
    private $duplicatedPublicCatalogSaveHandler;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog
     */
    private $catalogSaveHandler;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
     * @param \Magento\SharedCatalog\Model\SharedCatalogValidator $validator
     * @param \Magento\SharedCatalog\Model\SaveHandler\DuplicatedPublicSharedCatalog $duplicatedPublicCatalogSaveHandler
     * @param \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog $catalogSaveHandler
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement,
        \Magento\SharedCatalog\Model\SharedCatalogValidator $validator,
        \Magento\SharedCatalog\Model\SaveHandler\DuplicatedPublicSharedCatalog $duplicatedPublicCatalogSaveHandler,
        \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog $catalogSaveHandler,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerGroupManagement = $customerGroupManagement;
        $this->sharedCatalogManagement = $sharedCatalogManagement;
        $this->validator = $validator;
        $this->duplicatedPublicCatalogSaveHandler = $duplicatedPublicCatalogSaveHandler;
        $this->catalogSaveHandler = $catalogSaveHandler;
        $this->logger = $logger;
    }

    /**
     * Shared Catalog saving.
     *
     * Instantiate appropriate shared catalog saver and call its 'execute' method.
     * Roll back transaction in case if errors.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return SharedCatalogInterface
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function execute(SharedCatalogInterface $sharedCatalog)
    {
        $originalSharedCatalog = clone $sharedCatalog;
        try {
            $this->validator->validate($sharedCatalog);
            if ($this->validator->isCatalogPublicTypeDuplicated($sharedCatalog)) {
                $publicCatalog = $this->sharedCatalogManagement->getPublicCatalog();
                $sharedCatalog = $this->duplicatedPublicCatalogSaveHandler->execute($sharedCatalog, $publicCatalog);
            } else {
                $sharedCatalog = $this->catalogSaveHandler->execute($sharedCatalog, $originalSharedCatalog);
            }
        } catch (CouldNotSaveException $e) {
            $this->rollback($sharedCatalog, $originalSharedCatalog);
            throw $e;
        } catch (LocalizedException $e) {
            $this->rollback($sharedCatalog, $originalSharedCatalog);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->rollback($sharedCatalog, $originalSharedCatalog);
            throw new CouldNotSaveException(__('Could not save shared catalog.'));
        }
        return $sharedCatalog;
    }

    /**
     * Rollback saving transaction.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param SharedCatalogInterface $originalSharedCatalog
     * @throws CouldNotSaveException
     * @return void
     */
    private function rollback(
        SharedCatalogInterface $sharedCatalog,
        SharedCatalogInterface $originalSharedCatalog
    ) {
        if (!$originalSharedCatalog->getCustomerGroupId() && $sharedCatalog->getCustomerGroupId()) {
            try {
                $this->customerGroupManagement->deleteCustomerGroupById($sharedCatalog);
            } catch (LocalizedException $e) {
                $this->logger->critical($e->getMessage());
                throw new CouldNotSaveException(__('Could not save shared catalog.'));
            }
            $sharedCatalog->setCustomerGroupId(null);
        }
    }
}
