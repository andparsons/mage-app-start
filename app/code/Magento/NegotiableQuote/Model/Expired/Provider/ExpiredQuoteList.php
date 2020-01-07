<?php

namespace Magento\NegotiableQuote\Model\Expired\Provider;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class provides list of quotes that should be expired.
 */
class ExpiredQuoteList
{
    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var array
     */
    private $allowedStatuses = [
        NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
        NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN,
        NegotiableQuoteInterface::STATUS_CREATED,
        NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
        NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN
    ];

    /**
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        TimezoneInterface $localeDate
    ) {
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->localeDate = $localeDate;
    }

    /**
     * Get list of quotes that are going to expire in a day.
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface[]
     */
    public function getExpiredQuotes()
    {
        $currentDate = $this->localeDate->date();
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('extension_attribute_negotiable_quote.expiration_period')
                    ->setConditionType('lteq')
                    ->setValue($currentDate->format('Y-m-d'))
                    ->create(),
            ]
        );
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('extension_attribute_negotiable_quote.expiration_period')
                    ->setConditionType('neq')
                    ->setValue("0000-00-00")
                    ->create(),
            ]
        );
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('extension_attribute_negotiable_quote.status')
                    ->setConditionType('in')
                    ->setValue($this->allowedStatuses)
                    ->create(),
            ]
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->negotiableQuoteRepository->getList($searchCriteria)->getItems();
    }
}
