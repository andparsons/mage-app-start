<?php

namespace Magento\NegotiableQuote\Model\Query;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;

/**
 * Class for retrieving negotiable quote list.
 */
class GetList
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RestrictionInterface
     */
    private $restriction;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param RestrictionInterface $restriction
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchResultsFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        RestrictionInterface $restriction,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->restriction = $restriction;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get list of negotiable quotes and replace locked quote with snapshot if $snapshots equals true.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $snapshots [optional]
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $snapshots = false)
    {
        /** @var \Magento\Framework\Api\SearchResults $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);
        $collection->addFieldToFilter(
            'extension_attribute_negotiable_quote.is_regular_quote',
            ['eq' => 1]
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        $items = $collection->getItems();
        if ($snapshots) {
            $items = $this->replaceQuotesToSnapshots($items);
        }
        $searchResult->setItems($items);
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Replace quotes from search items with snapshot if quote is locked.
     *
     * @param \Magento\Framework\DataObject[] $items
     * @return mixed
     */
    private function replaceQuotesToSnapshots(array $items)
    {
        foreach ($items as $key => $quote) {
            $this->restriction->setQuote($quote);
            if ($this->restriction->isLockMessageDisplayed()) {
                $items[$key] = $this
                    ->negotiableQuoteManagement
                    ->getSnapshotQuote($quote->getId());
            }
        }
        return $items;
    }

    /**
     * Get list of negotiable quotes by customer id.
     *
     * @param int $customerId
     * @return \Magento\Framework\DataObject[]
     */
    public function getListByCustomerId($customerId)
    {
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);
        $collection->addFieldToFilter(
            'extension_attribute_negotiable_quote.is_regular_quote',
            ['eq' => 1]
        );
        $collection->addFieldToFilter(
            'main_table.customer_id',
            ['eq' => $customerId]
        );

        return $collection->getItems();
    }
}
