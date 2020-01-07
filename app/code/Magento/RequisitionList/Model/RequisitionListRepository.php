<?php

namespace Magento\RequisitionList\Model;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\Data\RequisitionListInterfaceFactory;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList as RequisitionListResource;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\Collection;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\CollectionFactory;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\RequisitionList\Model\RequisitionList\Items;
use Magento\RequisitionList\Model\Config as ModuleConfig;

/**
 * Requisition List repository object.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequisitionListRepository implements RequisitionListRepositoryInterface
{
    /**
     * @var RequisitionListInterface[]
     */
    private $instances = [];

    /**
     * Requisition List factory.
     *
     * @var RequisitionListInterfaceFactory
     */
    private $requisitionListFactory;

    /**
     * Requisition List resource model.
     *
     * @var RequisitionListResource
     */
    private $requisitionListResource;

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
     * @var Items
     */
    private $requisitionListItemRepository;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param RequisitionListInterfaceFactory $requisitionListFactory
     * @param RequisitionListResource $requisitionListResource
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param Items $requisitionListItemRepository
     * @param ModuleConfig $moduleConfig
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DateTime $dateTime
     */
    public function __construct(
        RequisitionListInterfaceFactory $requisitionListFactory,
        RequisitionListResource $requisitionListResource,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        Items $requisitionListItemRepository,
        ModuleConfig $moduleConfig,
        CollectionProcessorInterface $collectionProcessor,
        DateTime $dateTime
    ) {
        $this->requisitionListFactory = $requisitionListFactory;
        $this->requisitionListResource = $requisitionListResource;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->requisitionListItemRepository = $requisitionListItemRepository;
        $this->moduleConfig = $moduleConfig;
        $this->collectionProcessor = $collectionProcessor;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save(RequisitionListInterface $requisitionList, $processName = false)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(RequisitionListInterface::CUSTOMER_ID, $requisitionList->getCustomerId());
        if ($requisitionList->getId() === null
            && $collection->getSize() >= $this->moduleConfig->getMaxCountRequisitionList()
        ) {
            throw new CouldNotSaveException(
                __('Could not save Requisition List. Maximum number of requisition lists was exceeded.')
            );
        }

        $isValidId = true;

        if ($requisitionList->getId()) {
            $isValidId = $collection->addFieldToFilter(
                RequisitionListInterface::REQUISITION_LIST_ID,
                [
                    'eq' => $requisitionList->getEntityId()
                ]
            )->getItems();
        }

        if ($processName) {
            $this->processName($requisitionList);
        }

        $requisitionList->setUpdatedAt($this->dateTime->timestamp());

        try {
            if (!$isValidId) {
                throw new CouldNotSaveException(
                    __('Could not save requisition list. Invalid data provided')
                );
            }

            $this->requisitionListResource->save($requisitionList);

            $items = $requisitionList->getItems();
            $newItemIds = [];
            foreach ($items as $item) {
                $item->setRequisitionListId($requisitionList->getId());
                $this->requisitionListItemRepository->save($item);
                $newItemIds[] = $item->getId();
            }
            $this->deleteRemovedItems($requisitionList, $newItemIds);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save Requisition List'),
                $e
            );
        }
        unset($this->instances[$requisitionList->getId()]);
        return $requisitionList;
    }

    /**
     * Get old requisition list items
     *
     * @param RequisitionListInterface $requisitionList
     * @return array
     */
    private function getOldItemIds(RequisitionListInterface $requisitionList)
    {
        $oldItemIds = [];
        unset($this->instances[$requisitionList->getId()]);
        $oldItems = $this->get($requisitionList->getId())->getItems();
        foreach ($oldItems as $item) {
            $oldItemIds[] = $item->getId();
        }

        return $oldItemIds;
    }

    /**
     * Delete removed items from requisition list
     *
     * @param RequisitionListInterface $requisitionList
     * @param array $newItemIds
     * @return void
     */
    private function deleteRemovedItems(RequisitionListInterface $requisitionList, array $newItemIds)
    {
        $oldItemIds = $this->getOldItemIds($requisitionList);
        $itemsToDelete = array_diff($oldItemIds, $newItemIds);
        foreach ($itemsToDelete as $itemToDeleteId) {
            $itemToDelete = $this->requisitionListItemRepository->get($itemToDeleteId);
            $this->requisitionListItemRepository->delete($itemToDelete);
        }
    }

    /**
     * Processes name value of the requisition list
     *
     * @param RequisitionListInterface $requisitionList
     * @return void
     */
    private function processName(RequisitionListInterface $requisitionList)
    {
        if ($this->checkIfNameExists($requisitionList)) {
            $name = $this->generateNextName($requisitionList->getName(), false);
            $requisitionList->setName($name);
            while ($this->checkIfNameExists($requisitionList)) {
                $name = $this->generateNextName($name);
                $requisitionList->setName($name);
            }
        }
    }

    /**
     * Generates next name for name processing
     *
     * @param string $name
     * @param bool $checkNum
     * @return string
     */
    private function generateNextName($name, $checkNum = true)
    {
        $pattern = '/(.*\s)(\d+)$/si';
        if ($checkNum && preg_match($pattern, $name, $matches)) {
            $replacement = '${1}' . ($matches[2] + 1);
            return preg_replace($pattern, $replacement, $name);
        }
        return $name . ' 1';
    }

    /**
     * Checks if requisition list with given name already exists
     *
     * @param RequisitionListInterface $requisitionList
     * @return bool
     */
    private function checkIfNameExists(RequisitionListInterface $requisitionList)
    {
        $id = $requisitionList->getId();
        $name = $requisitionList->getName();
        $customerId = $requisitionList->getCustomerId();
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(RequisitionListInterface::REQUISITION_LIST_ID, ['neq' => $id]);
        $collection->addFieldToFilter(RequisitionListInterface::NAME, ['eq' => $name]);
        $collection->addFieldToFilter(RequisitionListInterface::CUSTOMER_ID, ['eq' => $customerId]);
        $collection->load();
        return (bool)$collection->getSize();
    }

    /**
     * @inheritdoc
     */
    public function get($requisitionListId)
    {
        if (!isset($this->instances[$requisitionListId])) {
            /** @var RequisitionListInterface $requisitionList */
            $requisitionList = $this->requisitionListFactory->create();
            $requisitionList->load($requisitionListId);
            if (!$requisitionList->getId()) {
                throw NoSuchEntityException::singleField('id', $requisitionListId);
            }
            $this->instances[$requisitionListId] = $requisitionList;
        }
        return $this->instances[$requisitionListId];
    }

    /**
     * @inheritdoc
     */
    public function delete(RequisitionListInterface $requisitionList)
    {
        try {
            $requisitionListId = $requisitionList->getId();
            $this->requisitionListResource->delete($requisitionList);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete Requisition List with id %1',
                    $requisitionList->getId()
                ),
                $e
            );
        }
        unset($this->instances[$requisitionListId]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($requisitionListId)
    {
        $requisitionList = $this->get($requisitionListId);
        return $this->delete($requisitionList);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
