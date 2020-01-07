<?php

namespace Magento\NegotiableQuote\Model;

use Psr\Log\LoggerInterface;
use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\History as HistoryResource;
use Magento\NegotiableQuote\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Exception\StateException;

/**
 * Class HistoryRepository
 */
class HistoryRepository implements HistoryRepositoryInterface
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\HistoryInterface[]
     */
    private $instances = [];

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\History
     */
    private $historyResource;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory
     */
    private $historyFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\History\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param HistoryResource $historyResource
     * @param HistoryInterfaceFactory $historyFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param HistoryCollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        HistoryResource $historyResource,
        HistoryInterfaceFactory $historyFactory,
        SearchResultsFactory $searchResultsFactory,
        HistoryCollectionFactory $collectionFactory,
        LoggerInterface $logger,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->historyResource = $historyResource;
        $this->historyFactory = $historyFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(HistoryInterface $historyLog)
    {
        try {
            $this->historyResource->save($historyLog);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save history log'),
                $e
            );
        }
        unset($this->instances[$historyLog->getHistoryId()]);
        return $historyLog->getHistoryId();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            /** @var HistoryInterface $history */
            $history = $this->historyFactory->create();
            $history->load($id);
            if (!$history->getHistoryId()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
            $this->instances[$id] = $history;
        }
        return $this->instances[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Framework\Api\SearchResults $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(HistoryInterface $historyLog)
    {
        try {
            $this->historyResource->delete($historyLog);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new StateException(
                __('Cannot delete history log with id %1', $historyLog->getEntityId()),
                $exception
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }
}
