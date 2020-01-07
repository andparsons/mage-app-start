<?php

namespace Magento\SharedCatalog\Model\ResourceModel\Permission\CategoryPermissions;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationListInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\SharedCatalog\Model\Permissions\Synchronizer;
use Magento\SharedCatalog\Model\SharedCatalogInvalidation;
use Psr\Log\LoggerInterface;

/**
 * Consumer for shared catalog permissions queue to update category permissions accordingly.
 */
class Consumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Synchronizer
     */
    private $permissionsSynchronizer;

    /**
     * @var SharedCatalogInvalidation
     */
    private $sharedCatalogInvalidation;

    /**
     * @param LoggerInterface $logger
     * @param EntityManager $entityManager
     * @param SerializerInterface $serializer
     * @param Synchronizer $permissionsSynchronizer
     * @param SharedCatalogInvalidation $sharedCatalogInvalidation
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManager $entityManager,
        SerializerInterface $serializer,
        Synchronizer $permissionsSynchronizer,
        SharedCatalogInvalidation $sharedCatalogInvalidation
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->permissionsSynchronizer = $permissionsSynchronizer;
        $this->sharedCatalogInvalidation = $sharedCatalogInvalidation;
    }

    /**
     * Processing batch operations for update category permissions from shared catalog.
     *
     * @param OperationListInterface $operationList
     * @return void
     * @throws \Magento\Framework\DB\Adapter\DuplicateException
     * @throws \LogicException
     * @throws \Exception
     */
    public function processOperations(OperationListInterface $operationList)
    {
        $updatedCategories = [];
        foreach ($operationList->getItems() as $operation) {
            $serializedData = $operation->getSerializedData();
            $unserializedData = $this->serializer->unserialize($serializedData);
            $categoryId = (int) $unserializedData['category_id'];
            $groupIds = explode(',', $unserializedData['group_ids']);
            $this->permissionsSynchronizer->updateCategoryPermissions($categoryId, $groupIds);
            $updatedCategories[] = $categoryId;

            $operation->setStatus(OperationInterface::STATUS_TYPE_COMPLETE);
            $operation->setResultMessage(null);
        }
        $this->sharedCatalogInvalidation->reindexCatalogPermissions($updatedCategories);
        $this->entityManager->save($operationList);
    }
}
