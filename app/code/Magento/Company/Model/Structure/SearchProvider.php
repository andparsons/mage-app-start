<?php

namespace Magento\Company\Model\Structure;

/**
 * Search Provider for Company Structure.
 */
class SearchProvider
{
    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Structure\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \Magento\Framework\Api\SearchResultsFactory                            $searchResultsFactory
     * @param \Magento\Company\Model\ResourceModel\Structure\CollectionFactory       $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface     $collectionProcessor
     */
    public function __construct(
        \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory,
        \Magento\Company\Model\ResourceModel\Structure\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Retrieve structures that match a specified search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \InvalidArgumentException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Framework\Api\SearchResults $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
