<?php

namespace Magento\CompanyCredit\Model;

/**
 * History repository interface.
 */
interface HistoryRepositoryInterface
{
    /**
     * Create credit limit.
     *
     * @param \Magento\CompanyCredit\Model\HistoryInterface $history
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\CompanyCredit\Model\HistoryInterface $history);

    /**
     * Get credit limit.
     *
     * @param int $historyId
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($historyId);

    /**
     * Delete credit limit.
     *
     * @param \Magento\CompanyCredit\Model\HistoryInterface $history
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Magento\CompanyCredit\Model\HistoryInterface $history);

    /**
     * Retrieve credit limits which match a specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
