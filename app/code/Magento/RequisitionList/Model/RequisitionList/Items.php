<?php

namespace Magento\RequisitionList\Model\RequisitionList;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\ResourceModel\RequisitionListItem as RequisitionListItemResource;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\CollectionFactory;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchResultsFactory;

/**
 * Requisition List Items
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Items
{
    /**
     * @var RequisitionListItemInterface[]
     */
    private $instances = [];

    /**
     * Requisition List factory.
     *
     * @var RequisitionListItemInterfaceFactory
     */
    private $requisitionListItemFactory;

    /**
     * Requisition List resource model.
     *
     * @var RequisitionListItemResource
     */
    private $requisitionListItemResource;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * RequisitionListRepository constructor.
     *
     * @param RequisitionListItemInterfaceFactory $requisitionListItemFactory
     * @param RequisitionListItemResource $requisitionListItemResource
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RequisitionListItemInterfaceFactory $requisitionListItemFactory,
        RequisitionListItemResource $requisitionListItemResource,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->requisitionListItemFactory = $requisitionListItemFactory;
        $this->requisitionListItemResource = $requisitionListItemResource;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Requisition List Item
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @return bool
     * @throws CouldNotSaveException
     */
    public function save(RequisitionListItemInterface $requisitionListItem)
    {
        try {
            $this->requisitionListItemResource->save($requisitionListItem);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save Requisition List'),
                $e
            );
        }
        unset($this->instances[$requisitionListItem->getId()]);
        return true;
    }

    /**
     * Get Requisition List item by ID
     *
     * @param int $requisitionListItemId
     * @return RequisitionListItemInterface
     * @throws NoSuchEntityException
     */
    public function get($requisitionListItemId)
    {
        if (!isset($this->instances[$requisitionListItemId])) {
            $requisitionListItem = $this->requisitionListItemFactory->create();
            $requisitionListItem->load($requisitionListItemId);
            if (!$requisitionListItem->getId()) {
                throw NoSuchEntityException::singleField('id', $requisitionListItemId);
            }
            $this->instances[$requisitionListItemId] = $requisitionListItem;
        }
        return $this->instances[$requisitionListItemId];
    }

    /**
     * Delete Requisition List item
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @return bool
     * @throws StateException
     */
    public function delete(RequisitionListItemInterface $requisitionListItem)
    {
        try {
            $requisitionListItemId = $requisitionListItem->getId();
            $this->requisitionListItemResource->delete($requisitionListItem);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete Requisition List with id %1',
                    $requisitionListItem->getId()
                ),
                $e
            );
        }
        unset($this->instances[$requisitionListItemId]);
        return true;
    }

    /**
     * Delete Requisition List item ID
     *
     * @param int $requisitionListItemId
     * @return bool
     */
    public function deleteById($requisitionListItemId)
    {
        $requisitionListItem = $this->get($requisitionListItemId);
        return $this->delete($requisitionListItem);
    }

    /**
     * Get list of Requisition List items
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
