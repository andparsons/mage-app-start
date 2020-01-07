<?php
namespace Magento\NegotiableQuote\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\NegotiableQuote\Model\NegotiableQuote;
use Magento\NegotiableQuote\Model\Quote\Address;

/**
 * Class DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address
     */
    private $negotiableQuoteAddress;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteRepository
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structure;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param \Magento\NegotiableQuote\Model\NegotiableQuoteRepository $negotiableQuoteRepository
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param Address $negotiableQuoteAddress
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Company\Model\Company\Structure $structure
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\NegotiableQuote\Model\NegotiableQuoteRepository $negotiableQuoteRepository,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        Address $negotiableQuoteAddress,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Company\Model\Company\Structure $structure,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->userContext = $userContext;
        $this->negotiableQuoteAddress = $negotiableQuoteAddress;
        $this->storeManager = $storeManager;
        $this->structure = $structure;
        $this->request = $request;
        $this->authorization = $authorization;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->formatOutput($this->getSearchResult());
    }

    /**
     * Returns Search result.
     *
     * @return SearchResultsInterface
     */
    public function getSearchResult()
    {
        $this->addOrder('entity_id', 'DESC');
        $customerId = $this->getCustomerId();
        $allTeamIds = [];
        if ($this->authorization->isAllowed('Magento_NegotiableQuote::view_quotes_sub')) {
            $allTeamIds = $this->structure->getAllowedChildrenIds($customerId);
        }
        $allTeamIds[] = $customerId;
        $filter = $this->filterBuilder
            ->setField('main_table.customer_id')
            ->setConditionType('in')
            ->setValue(array_unique($allTeamIds))
            ->create();
        $this->searchCriteriaBuilder->addFilter($filter);
        $filter = $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('in')
            ->setValue($this->storeManager->getStore()->getWebsite()->getStoreIds())
            ->create();
        $this->searchCriteriaBuilder->addFilter($filter);
        $this->searchCriteria = $this->searchCriteriaBuilder->create();
        $this->searchCriteria->setRequestName($this->name);

        return $this->negotiableQuoteRepository->getList($this->getSearchCriteria(), true);
    }

    /**
     * @return int|null
     */
    private function getCustomerId()
    {
        return $this->userContext->getUserId() ? : null;
    }

    /**
     * @param SearchResultsInterface $searchResult
     * @return array
     */
    private function formatOutput(SearchResultsInterface $searchResult)
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $this->negotiableQuoteAddress->updateQuoteShippingAddressDraft($item->getId());
            $itemData = [];
            foreach ($item->getData() as $key => $value) {
                $itemData[$key] = $value;
            }
            $itemData = $this->addExtensionAttributes($item, $itemData);
            $arrItems['items'][] = $itemData;
        }
        return $arrItems;
    }

    /**
     * @param \Magento\Framework\Api\ExtensibleDataInterface $item
     * @param array $itemData
     * @return array
     */
    private function addExtensionAttributes(\Magento\Framework\Api\ExtensibleDataInterface $item, $itemData = [])
    {
        $extensionAttributes = $item->getExtensionAttributes();
        if (!is_object($extensionAttributes)) {
            return $itemData;
        }
        /** @var NegotiableQuote $negotiableQuote */
        $negotiableQuote = $extensionAttributes->getNegotiableQuote();
        if (!is_object($negotiableQuote)) {
            return $itemData;
        }

        foreach ($negotiableQuote->getData() as $key => $value) {
            $itemData[$key] = $value;
        }

        return $itemData;
    }
}
