<?php

namespace Magento\Company\Model\Team;

use Magento\Company\Model\ResourceModel\Team\CollectionFactory as TeamCollectionFactory;

/**
 * Class for retrieving lists of team model entities based on a given search criteria.
 */
class GetList
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Team\CollectionFactory
     */
    private $teamCollectionFactory;

    /**
     * @var \Magento\Company\Api\Data\TeamSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param TeamCollectionFactory $teamCollectionFactory
     * @param \Magento\Company\Api\Data\TeamSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TeamCollectionFactory $teamCollectionFactory,
        \Magento\Company\Api\Data\TeamSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->teamCollectionFactory = $teamCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Returns the list of teams for the specified search criteria (team name or description).
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magento\Company\Api\Data\TeamSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->teamCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }
}
