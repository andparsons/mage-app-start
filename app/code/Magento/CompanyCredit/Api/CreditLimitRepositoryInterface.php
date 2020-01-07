<?php

namespace Magento\CompanyCredit\Api;

/**
 * Interface for credit limit repository for CRUD operations.
 *
 * @api
 * @since 100.0.0
 */
interface CreditLimitRepositoryInterface
{
    /**
     * Update the following company credit attributes: credit currency, credit limit and
     * setting to exceed credit.
     *
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(\Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit);

    /**
     * Returns data on the credit limit for a specified credit limit ID.
     *
     * @param int $creditId
     * @param bool $reload [optional]
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($creditId, $reload = false);

    /**
     * Delete credit limit.
     *
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit);

    /**
     * Returns the list of credits for specified companies.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \LogicException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
