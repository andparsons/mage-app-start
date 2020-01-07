<?php

namespace Magento\RequisitionList\Api;

use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface RequisitionListRepositoryInterface
 *
 * @api
 * @since 100.0.0
 */
interface RequisitionListRepositoryInterface
{
    /**
     * Save Requisition List
     *
     * @param \Magento\RequisitionList\Api\Data\RequisitionListInterface $requisitionList
     * @param bool $processName
     * @return \Magento\RequisitionList\Api\Data\RequisitionListInterface
     * @throws CouldNotSaveException
     */
    public function save(RequisitionListInterface $requisitionList, $processName = false);

    /**
     * Get Requisition List by ID
     *
     * @param int $requisitionListId
     * @return \Magento\RequisitionList\Api\Data\RequisitionListInterface
     * @throws NoSuchEntityException
     */
    public function get($requisitionListId);

    /**
     * Delete Requisition List
     *
     * @param RequisitionListInterface $requisitionList
     * @return bool
     * @throws StateException
     */
    public function delete(RequisitionListInterface $requisitionList);

    /**
     * Delete Requisition List ID
     *
     * @param int $requisitionListId
     * @return bool
     */
    public function deleteById($requisitionListId);

    /**
     * Get list of Requisition List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
