<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Interface HistoryRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface HistoryRepositoryInterface
{
    /**
     * Save the history log of the negotiated quote update
     *
     * @param HistoryInterface $historyLog log.
     * @return bool
     * @throws CouldNotSaveException
     */
    public function save(HistoryInterface $historyLog);

    /**
     * Get history log entity by ID
     *
     * @param int $id
     * @return HistoryInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Get list of history logs
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete history log.
     *
     * @param HistoryInterface $historyLog
     * @return bool
     * @throws StateException
     */
    public function delete(HistoryInterface $historyLog);

    /**
     * Delete history log by ID
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}
