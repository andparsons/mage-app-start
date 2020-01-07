<?php

namespace Magento\CompanyCredit\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;

/**
 * Repository for history credit limit.
 */
class HistoryRepository implements HistoryRepositoryInterface
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryInterface[]
     */
    private $instances = [];

    /**
     * @var \Magento\CompanyCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\History
     */
    private $historyResource;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\CompanyCredit\Model\Email\Sender
     */
    private $emailSender;

    /**
     * HistoryRepository constructor.
     *
     * @param \Magento\CompanyCredit\Model\HistoryFactory $historyFactory
     * @param \Magento\CompanyCredit\Model\ResourceModel\History $historyResource
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param \Magento\CompanyCredit\Model\Email\Sender $emailSender
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Magento\CompanyCredit\Model\HistoryFactory $historyFactory,
        \Magento\CompanyCredit\Model\ResourceModel\History $historyResource,
        HistoryCollectionFactory $historyCollectionFactory,
        \Magento\CompanyCredit\Model\Email\Sender $emailSender,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->emailSender = $emailSender;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\CompanyCredit\Model\HistoryInterface $history)
    {
        try {
            $this->historyResource->save($history);
            $this->emailSender->sendCompanyCreditChangedNotificationEmail($history);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save history'),
                $e
            );
        }

        return $history;
    }

    /**
     * {@inheritdoc}
     */
    public function get($historyId)
    {
        if (!isset($this->instances[$historyId])) {
            /** @var \Magento\CompanyCredit\Model\HistoryInterface $history */
            $history = $this->historyFactory->create();
            $this->historyResource->load($history, $historyId);
            if (!$history->getId()) {
                throw NoSuchEntityException::singleField('id', $historyId);
            }
            $this->instances[$historyId] = $history;
        }
        return $this->instances[$historyId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\CompanyCredit\Model\HistoryInterface $history)
    {
        try {
            $id = $history->getId();
            $this->historyResource->delete($history);
            unset($this->instances[$id]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Cannot delete history with id %1',
                    $history->getId()
                ),
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \Magento\CompanyCredit\Model\ResourceModel\History\Collection $collection */
        $collection = $this->historyCollectionFactory->create();

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    $sortOrder->getDirection() ? : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }
}
