<?php

namespace Magento\CompanyCredit\Api;

/**
 * Update credit history log and retrieve history which match a specified criteria.
 *
 * @api
 * @since 100.0.0
 */
interface CreditHistoryManagementInterface
{
    /**
     * Update the PO Number and/or comment for a Reimburse transaction.
     *
     * @param int $historyId
     * @param string|null $purchaseOrder [optional]
     * @param string|null $comment [optional]
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return bool true on success
     */
    public function update($historyId, $purchaseOrder = null, $comment = null);

    /**
     * Returns the credit history for one or more companies.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\CompanyCredit\Api\Data\HistorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
