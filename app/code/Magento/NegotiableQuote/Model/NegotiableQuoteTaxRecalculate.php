<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuotePrice\ScheduleBulk;
use Magento\Authorization\Model\UserContextInterface as UserContext;
use Magento\Framework\App\ObjectManager;

/**
 * Class NegotiableQuoteTaxRecalculate
 */
class NegotiableQuoteTaxRecalculate
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * @var ScheduleBulk|null
     */
    private $scheduleBulk;

    /**
     * @var UserContext|null
     */
    private $userContext;

    /**
     * @var bool
     */
    protected $needRecalculate = false;

    /**
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScheduleBulk|null $scheduleBulk
     * @param UserContext|null $userContext
     */
    public function __construct(
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScheduleBulk $scheduleBulk = null,
        UserContext $userContext = null
    ) {
        $this->quoteItemManagement = $quoteItemManagement;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scheduleBulk = $scheduleBulk ?: ObjectManager::getInstance()->get(ScheduleBulk::class);
        $this->userContext = $userContext ?: ObjectManager::getInstance()->get(UserContext::class);
    }

    /**
     * Set need recalculate
     *
     * @param bool $needRecalculate
     * @return $this
     */
    public function setNeedRecalculate($needRecalculate): NegotiableQuoteTaxRecalculate
    {
        $this->needRecalculate = $needRecalculate;
        return $this;
    }

    /**
     * Recalculate tax on all negotiable quote.
     *
     * @param bool $needRecalculate
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function recalculateTax($needRecalculate = false): void
    {
        if ($this->needRecalculate || $needRecalculate) {
            $filter = $this->filterBuilder
                ->setField('extension_attribute_negotiable_quote.status')
                ->setConditionType('nin')
                ->setValue([NegotiableQuoteInterface::STATUS_ORDERED, NegotiableQuoteInterface::STATUS_CLOSED])
                ->create();
            $this->searchCriteriaBuilder->addSortOrder('entity_id', 'DESC');
            $this->searchCriteriaBuilder->addFilter($filter);
            $searchCriteria = $this->searchCriteriaBuilder->create();

            $quotes = $this->negotiableQuoteRepository->getList($searchCriteria)->getItems();

            $this->scheduleBulk->execute($quotes, $this->userContext->getUserId());
        }
    }
}
