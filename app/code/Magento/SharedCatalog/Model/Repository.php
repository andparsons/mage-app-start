<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;

/**
 * Shared catalog repository.
 */
class Repository implements SharedCatalogRepositoryInterface
{
    /**
     * List of shared Catalogs.
     *
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface[]
     */
    private $instances = [];

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog
     */
    private $sharedCatalogResource;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory
     */
    private $sharedCatalogCollectionFactory;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface
     */
    private $sharedCatalogProductItemManagement;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogValidator
     */
    private $validator;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler
     */
    private $saveHandler;

    /**
     * Repository constructor.
     *
     * @param ResourceModel\SharedCatalog $sharedCatalogResource
     * @param ResourceModel\SharedCatalog\CollectionFactory $sharedCatalogCollectionFactory
     * @param \Magento\SharedCatalog\Api\Data\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\SharedCatalog\Api\ProductItemManagementInterface $sharedCatalogProductItemManagement
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Magento\SharedCatalog\Model\SharedCatalogValidator $validator
     * @param \Magento\SharedCatalog\Model\SaveHandler $saveHandler
     */
    public function __construct(
        \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog $sharedCatalogResource,
        \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory $sharedCatalogCollectionFactory,
        \Magento\SharedCatalog\Api\Data\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\SharedCatalog\Api\ProductItemManagementInterface $sharedCatalogProductItemManagement,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        \Magento\SharedCatalog\Model\SharedCatalogValidator $validator,
        \Magento\SharedCatalog\Model\SaveHandler $saveHandler
    ) {
        $this->sharedCatalogResource = $sharedCatalogResource;
        $this->sharedCatalogCollectionFactory = $sharedCatalogCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->sharedCatalogProductItemManagement = $sharedCatalogProductItemManagement;
        $this->collectionProcessor = $collectionProcessor;
        $this->validator = $validator;
        $this->saveHandler = $saveHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        if ($sharedCatalog->getId()) {
            $prevSharedCatalogData = $sharedCatalog->getData();
            $sharedCatalog = $this->get($sharedCatalog->getId());
            $sharedCatalog->setData(array_merge($sharedCatalog->getData(), $prevSharedCatalogData));
        }
        $sharedCatalog = $this->saveHandler->execute($sharedCatalog);
        unset($this->instances[$sharedCatalog->getId()]);
        return $sharedCatalog->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function get($sharedCatalogId)
    {
        if (!isset($this->instances[$sharedCatalogId])) {
            /** @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection $collection */
            $collection = $this->sharedCatalogCollectionFactory->create();
            $collection->addFieldToFilter('entity_id', ['eq' => $sharedCatalogId]);
            /** @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog */
            $sharedCatalog = $collection->getFirstItem();
            $this->validator->checkSharedCatalogExist($sharedCatalog);
            $this->instances[$sharedCatalogId] = $sharedCatalog;
        }
        return $this->instances[$sharedCatalogId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog)
    {
        if ($this->validator->isSharedCatalogPublic($sharedCatalog)) {
            try {
                $sharedCatalogId = $sharedCatalog->getId();
                $this->sharedCatalogResource->delete($sharedCatalog);
                $this->sharedCatalogProductItemManagement->deleteItems($sharedCatalog);
                unset($this->instances[$sharedCatalogId]);
            } catch (\Exception $e) {
                throw new \Exception(
                    __(
                        'Cannot delete shared catalog with id %1',
                        $sharedCatalog->getId()
                    ),
                    0,
                    $e
                );
            }
        };
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($sharedCatalogId)
    {
        $sharedCatalog = $this->get($sharedCatalogId);
        $this->delete($sharedCatalog);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /* @var \Magento\SharedCatalog\Api\Data\SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection $collection */
        $collection = $this->sharedCatalogCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }
}
