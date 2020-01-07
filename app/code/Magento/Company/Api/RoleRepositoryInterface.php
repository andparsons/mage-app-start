<?php

namespace Magento\Company\Api;

/**
 * A repository interface for role entity that provides basic CRUD operations.
 *
 * @api
 * @since 100.0.0
 */
interface RoleRepositoryInterface
{
    /**
     * Returns the list of roles and permissions for a specified company.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Company\Api\Data\RoleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Create or update a role for a selected company.
     *
     * @param \Magento\Company\Api\Data\RoleInterface $role
     * @return \Magento\Company\Api\Data\RoleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(\Magento\Company\Api\Data\RoleInterface $role);

    /**
     * Delete a role.
     *
     * @param int $roleId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete($roleId);

    /**
     * Returns the list of permissions for a specified role.
     *
     * @param int $roleId
     * @return \Magento\Company\Api\Data\RoleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($roleId);
}
