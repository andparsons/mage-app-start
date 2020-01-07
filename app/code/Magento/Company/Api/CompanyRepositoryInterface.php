<?php

namespace Magento\Company\Api;

/**
 * A repository interface for company entity that provides basic CRUD operations.
 *
 * @api
 * @since 100.0.0
 */
interface CompanyRepositoryInterface
{
    /**
     * Create or update a company account.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(\Magento\Company\Api\Data\CompanyInterface $company);

    /**
     * Returns company details.
     *
     * @param int $companyId
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($companyId);

    /**
     * Removes company entity and all the related links from the system.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Magento\Company\Api\Data\CompanyInterface $company);

    /**
     * Delete a company. Customers belonging to a company are not deleted with this request.
     *
     * @param int $companyId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($companyId);

    /**
     * Returns the list of companies. The list is an array of objects, and detailed information about item attributes
     * might not be included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Company\Api\Data\CompanySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
