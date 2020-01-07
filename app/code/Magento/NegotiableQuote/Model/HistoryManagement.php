<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\NegotiableQuote\Model\History\CriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class is responsible for managing negotiable quote history log.
 */
class HistoryManagement implements HistoryManagementInterface
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory
     */
    private $historyFactory;

    /**
     * @var HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\History\SnapshotManagement
     */
    private $snapshotManagement;

    /**
     * @var CriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\History\DiffProcessor
     */
    private $diffProcessor;

    /**
     * Json Serializer instance
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param HistoryRepositoryInterface $historyRepository
     * @param \Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory $historyFactory
     * @param History\SnapshotManagement $snapshotManagement
     * @param CriteriaBuilder $criteriaBuilder
     * @param \Magento\NegotiableQuote\Model\History\DiffProcessor $diffProcessor
     * @param Json|null $serializer [optional]
     */
    public function __construct(
        HistoryRepositoryInterface $historyRepository,
        \Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory $historyFactory,
        History\SnapshotManagement $snapshotManagement,
        CriteriaBuilder $criteriaBuilder,
        \Magento\NegotiableQuote\Model\History\DiffProcessor $diffProcessor,
        Json $serializer = null
    ) {
        $this->historyRepository = $historyRepository;
        $this->historyFactory = $historyFactory;
        $this->snapshotManagement = $snapshotManagement;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->diffProcessor = $diffProcessor;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * {@inheritdoc}
     */
    public function createLog($quoteId)
    {
        if ($quoteId) {
            $snapshotData = $this->snapshotManagement->collectSnapshotDataForNewQuote($quoteId);
            $data = $this->snapshotManagement->prepareCommentData($quoteId, $snapshotData);
            $this->addLog($quoteId, HistoryInterface::STATUS_CREATED, $snapshotData, $data);
            $this->updateDraftLogs($quoteId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateLog($quoteId, $isSeller = false, $status = '')
    {
        if ($quoteId) {
            $snapshotData = $this->snapshotManagement->collectSnapshotData($quoteId);
            $oldSnapshot = $this->getLastSnapshot($quoteId);
            $data = $this->snapshotManagement->getSnapshotsDiff($oldSnapshot, $snapshotData);
            if ($status == NegotiableQuoteInterface::STATUS_DECLINED) {
                unset($data['address']);
            }
            if ($data && is_array($data)) {
                $this->addLog($quoteId, HistoryInterface::STATUS_UPDATED, $snapshotData, $data, $isSeller);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addItemRemoveCatalogLog($quoteId, $productId)
    {
        if ($quoteId) {
            $oldSnapshot = $this->getLastSnapshot($quoteId);
            $snapshotData = $oldSnapshot;
            if (!empty($snapshotData['cart'])) {
                foreach ($snapshotData['cart'] as $key => $item) {
                    if ($item['product_id'] == $productId) {
                        unset($snapshotData['cart'][$key]);
                    }
                }
                $data = $this->diffProcessor->processDiff($oldSnapshot, $snapshotData);
                if (!empty($data['removed_from_cart'])) {
                    $data['removed_from_catalog'] = $data['removed_from_cart'];
                    unset($data['removed_from_cart']);
                }
                if ($data && is_array($data)) {
                    $this->saveLog($quoteId, $snapshotData, $data, false, true);
                    $this->updateDraftLogs($quoteId, true);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function closeLog($quoteId)
    {
        if ($quoteId) {
            $this->addLog($quoteId, HistoryInterface::STATUS_CLOSED);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatusLog($quoteId, $isSeller = false, $isExpired = false)
    {
        if ($quoteId) {
            $snapshotData = $this->getLastSnapshot($quoteId);
            $data = $this->diffStatus($quoteId, $snapshotData);
            if (isset($data['status']['new_value'])) {
                $snapshotData['status'] = $data['status']['new_value'];
                $this->addLog(
                    $quoteId,
                    HistoryInterface::STATUS_UPDATED,
                    $snapshotData,
                    $data,
                    $isSeller,
                    $isExpired
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomLog($quoteId, array $values, $isSeller = false, $isSystem = false)
    {
        if ($quoteId) {
            $snapshotData = $this->getLastSnapshot($quoteId);
            $data = [];
            foreach ($values as $value) {
                if ($value) {
                    $data['custom_log'][] = $value;
                }
            }
            $this->saveLog($quoteId, $snapshotData, $data, $isSeller, $isSystem);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteHistory($quoteId)
    {
        $searchCriteria = $this->criteriaBuilder->getQuoteHistoryCriteria($quoteId);
        return $this->historyRepository->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogUpdatesList($logId)
    {
        $lastLog = $this->getLog($logId);
        /** @var \Magento\NegotiableQuote\Model\History $lastLog */
        if ($lastLog && $lastLog->getLogData()) {
            return $this->serializer->unserialize($lastLog->getLogData());
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function updateDraftLogs($quoteId, $updateLastLog = false)
    {
        if ($quoteId) {
            $historyLogs = $this->getQuoteHistory($quoteId);
            if ($updateLastLog) {
                $historyLog = array_pop($historyLogs);
                $historyLog->setIsDraft(0);
                $this->historyRepository->save($historyLog);
            } else {
                foreach ($historyLogs as $historyLog) {
                    $historyLog->setIsDraft(0);
                    $this->historyRepository->save($historyLog);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSystemLogsStatus($quoteId)
    {
        if ($quoteId) {
            $searchCriteria = $this->criteriaBuilder->getSystemHistoryCriteria($quoteId);
            if ($this->historyRepository->getList($searchCriteria)->getTotalCount() > 0) {
                $systemLogs = $this->historyRepository->getList($searchCriteria)->getItems();
                foreach ($systemLogs as $historyLog) {
                    $historyLog->setStatus(HistoryInterface::STATUS_UPDATED);
                    $this->historyRepository->save($historyLog);
                }
            }
        }
    }

    /**
     * Get last snapshot array.
     *
     * @param int $quoteId
     * @return array
     */
    private function getLastSnapshot($quoteId)
    {
        $lastLog = $this->getLastLog($quoteId);
        /** @var \Magento\NegotiableQuote\Model\History $lastLog */
        if ($lastLog && $lastLog->getSnapshotData()) {
            return $this->serializer->unserialize($lastLog->getSnapshotData());
        }
        return [];
    }

    /**
     * Get last log record from DB.
     *
     * @param int $quoteId
     * @return HistoryInterface|null
     */
    private function getLastLog($quoteId)
    {
        if ($quoteId) {
            $historyList = $this->getQuoteHistory($quoteId);
            if (is_array($historyList) && (count($historyList) > 0)) {
                return array_pop($historyList);
            }
        }
        return null;
    }

    /**
     * Get log record from DB by ID.
     *
     * @param int $logId
     * @return HistoryInterface|null
     */
    private function getLog($logId)
    {
        if ($logId) {
            return $this->historyRepository->get($logId);
        }
        return null;
    }

    /**
     * Get difference data for quote status.
     *
     * @param int $quoteId
     * @param array $snapshotData
     * @return array
     */
    private function diffStatus($quoteId, array $snapshotData)
    {
        $statusDiff = [];
        $quote = $this->snapshotManagement->getQuote($quoteId);
        if (($quote !== null) && ($quote->getId())) {
            $newStatus = $quote->getExtensionAttributes()->getNegotiableQuote()->getStatus();
            if (array_key_exists('status', $snapshotData) && $snapshotData['status']) {
                if ($snapshotData['status'] != $newStatus) {
                    $statusDiff['status'] = [
                        'old_value' => $snapshotData['status'],
                        'new_value' => $newStatus
                    ];
                }
            } else {
                $statusDiff['status'] = [
                    'new_value' => $newStatus
                ];
            }
        }
        return $statusDiff;
    }

    /**
     * Save history log to the database.
     *
     * @param int $quoteId
     * @param string $status
     * @param array $snapshotData [optional]
     * @param array $data [optional]
     * @param bool $isSeller [optional]
     * @param bool $isExpired [optional]
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function addLog(
        $quoteId,
        $status,
        array $snapshotData = [],
        array $data = [],
        $isSeller = false,
        $isExpired = false
    ) {
        $systemData = [];
        $searchCriteria = $this->criteriaBuilder->getQuoteSearchCriteria($quoteId);
        $quote = $this->snapshotManagement->getQuoteForRemovedItem($searchCriteria);
        if (($quote !== null) && ($quote->getId())) {
            try {
                $customerId = $this->snapshotManagement->getCustomerId($quote, $isSeller, $isExpired);
                /** @var \Magento\NegotiableQuote\Model\History $historyLog */
                $historyLog = $this->historyFactory->create();
                $historyLog->setQuoteId($quoteId)
                    ->setIsSeller($isSeller)
                    ->setAuthorId($customerId)
                    ->setStatus($status);
                if (!empty($data)) {
                    $data = $this->snapshotManagement->checkForSystemLogs($data);
                    if (isset($data['system_data'])) {
                        $systemData = $data['system_data'];
                        unset($data['system_data']);
                    }
                    $historyLog->setLogData($this->serializer->serialize($data));
                }
                if (!empty($snapshotData)) {
                    $historyLog->setSnapshotData($this->serializer->serialize($snapshotData));
                }
                $this->historyRepository->save($historyLog);
                if ($systemData && !empty($systemData)) {
                    $this->saveLog($quoteId, $snapshotData, $systemData, false, true);
                }
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There was an error saving history log.'));
            }
        }
    }

    /**
     * Save system log.
     *
     * @param int $quoteId
     * @param array $snapshotData
     * @param array $data
     * @param bool $isSeller [optional]
     * @param bool $isExpired [optional]
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function saveLog($quoteId, array $snapshotData, array $data, $isSeller = false, $isExpired = false)
    {
        $lastLog = $this->getLastLog($quoteId);
        if ($lastLog->getStatus() == HistoryInterface::STATUS_UPDATED_BY_SYSTEM) {
            $updatedData = $this->mergeLogData($data, $this->serializer->unserialize($lastLog->getLogData()));
            $this->updateSystemLog($lastLog, $snapshotData, $updatedData);
        } else {
            $this->addLog(
                $quoteId,
                HistoryInterface::STATUS_UPDATED_BY_SYSTEM,
                $snapshotData,
                $data,
                $isSeller,
                $isExpired
            );
        }
    }

    /**
     * Merge system log data.
     *
     * @param array $newLogData
     * @param array $lastLogData
     * @return array
     */
    private function mergeLogData(array $newLogData, array $lastLogData)
    {
        $mergedArray = array_replace_recursive($newLogData, $lastLogData);
        if (isset($newLogData['custom_log']) && isset($lastLogData['custom_log'])) {
            $mergedArray['custom_log'] = $this->mergeCustomLog($lastLogData['custom_log'], $newLogData['custom_log']);
        }

        return $mergedArray;
    }

    /**
     * Merge custom log values from old and new log data.
     *
     * @param array $lastLogData
     * @param array $newLogData
     * @return array
     */
    private function mergeCustomLog(array $lastLogData, array $newLogData)
    {
        $result = [];
        foreach ($lastLogData as $log) {
            if (isset($log['field_id'])) {
                $keyNew = array_search($log['field_id'], array_column($newLogData, 'field_id'));
                if (isset($keyNew) && $keyNew !== false) {
                    $newLog = $newLogData[$keyNew];
                    unset($newLogData[$keyNew]);
                    $newLogData = array_values($newLogData);
                    $log = $this->mergeLogChild($log, $newLog);
                }
            }
            $result[] = $log;
        }
        $result = array_merge($result, $newLogData);

        return $result;
    }

    /**
     * Merge child values of log fields.
     *
     * @param array $lastLogData
     * @param array $newLogData
     * @return array
     */
    private function mergeLogChild(array $lastLogData, array $newLogData)
    {
        if (isset($newLogData['values']) && isset($lastLogData['values'])) {
            $lastLogData['values'] = $this->mergeCustomLog($lastLogData['values'], $newLogData['values']);
        } else {
            if (isset($newLogData['new_value'])) {
                $lastLogData['new_value'] = $newLogData['new_value'];
            } else {
                unset($lastLogData['new_value']);
            }
        }
        return $lastLogData;
    }

    /**
     * Update system history log record.
     *
     * @param HistoryInterface $lastLog
     * @param array $snapshotData
     * @param array $updatedData
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function updateSystemLog(HistoryInterface $lastLog, array $snapshotData, array $updatedData)
    {
        if ($lastLog != null) {
            try {
                if (!empty($updatedData)) {
                    $lastLog->setLogData($this->serializer->serialize($updatedData));
                }
                if (!empty($snapshotData)) {
                    $lastLog->setSnapshotData($this->serializer->serialize($snapshotData));
                }
                return $this->historyRepository->save($lastLog);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There was an error during updating history log.'));
            }
        }
        return false;
    }
}
