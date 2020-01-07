<?php
namespace Magento\QuickOrder\Model;

/**
 * Search among products using fulltext engine.
 */
class FulltextSearch
{
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Search\Api\SearchInterface
     */
    private $search;

    /**
     * @var \Magento\Framework\Api\Search\SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * @var int
     */
    private $resultLimit;

    /**
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Search\Api\SearchInterface $search
     * @param \Magento\Framework\Api\Search\SearchResultFactory $searchResultFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $searchRequestName
     * @param int $resultLimit [optional]
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Search\Api\SearchInterface $search,
        \Magento\Framework\Api\Search\SearchResultFactory $searchResultFactory,
        \Psr\Log\LoggerInterface $logger,
        $searchRequestName,
        $resultLimit = 500
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->search = $search;
        $this->searchResultFactory = $searchResultFactory;
        $this->logger = $logger;
        $this->searchRequestName = $searchRequestName;
        $this->resultLimit = $resultLimit;
    }

    /**
     * Full text search among products by their name and SKU.
     *
     * @param string $query
     * @param int $page
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function search($query, $page)
    {
        $this->filterBuilder->setField('search_term');
        $this->filterBuilder->setValue($query);
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setRequestName($this->searchRequestName);
        $searchCriteria->setCurrentPage($page);
        $searchCriteria->setPageSize($this->resultLimit);
        try {
            $searchResult = $this->search->search($searchCriteria);
        } catch (\Magento\Framework\Search\Request\EmptyRequestDataException $e) {
            /** @var \Magento\Framework\Api\Search\SearchResultInterface $searchResult */
            $searchResult = $this->searchResultFactory->create()->setItems([]);
        } catch (\Magento\Framework\Search\Request\NonExistingRequestNameException $e) {
            $this->logger->error($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('An error occurred. For details, see the error log.')
            );
        }

        return $searchResult->getItems();
    }
}
