<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Model\CreditLimit;

/**
 * Search Provider for Credit Limit entity.
 */
class SearchProvider
{
    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit\CollectionFactory
     */
    private $creditLimitCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\CompanyCredit\Model\ResourceModel\CreditLimit\CollectionFactory $creditLimitCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\CompanyCredit\Model\ResourceModel\CreditLimit\CollectionFactory $creditLimitCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->creditLimitCollectionFactory = $creditLimitCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * Returns the list of credits by search criterias.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitSearchResultsInterface
     * @throws \LogicException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\CompanyCredit\Api\Data\CreditLimitSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit\Collection $collection */
        $collection = $this->creditLimitCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    $sortOrder->getDirection() ? : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }
}
