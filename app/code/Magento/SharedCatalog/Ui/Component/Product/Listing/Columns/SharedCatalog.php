<?php
namespace Magento\SharedCatalog\Ui\Component\Product\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SharedCatalog\Api\ProductItemManagementInterface;
use Magento\SharedCatalog\Api\ProductItemRepositoryInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;

class SharedCatalog extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var ProductItemRepositoryInterface
     */
    private $productItemRepositoryInterface;

    /**
     * SharedCatalog constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param ProductItemRepositoryInterface $productItemRepositoryInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        ProductItemRepositoryInterface $productItemRepositoryInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->productItemRepositoryInterface = $productItemRepositoryInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $fieldName = $this->getData('name');
        $skus = array_column($dataSource['data']['items'], 'sku');
        $productItems = $this->getProductItems($skus);
        foreach ($dataSource['data']['items'] as &$item) {
            $item[$fieldName] = (isset($productItems[$item['sku']])) ? $productItems[$item['sku']] : '';
        }
        return $dataSource;
    }

    /**
     * Get linked products with SharedCatalog id
     *
     * @param array $skus
     * @return array
     */
    private function getProductItems(array $skus)
    {
        $productItems = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $skus, 'in')
            ->addFilter('customer_group_id', ProductItemManagementInterface::CUSTOMER_GROUP_NOT_LOGGED_IN, 'neq')
            ->create();
        $items = $this->productItemRepositoryInterface->getList($searchCriteria)->getItems();
        $sharedCatalogList = $this->getSharedCatalogList();
        foreach ($items as $item) {
            if (!empty($sharedCatalogList[$item->getCustomerGroupId()])) {
                $productItems[$item->getSku()][] = $sharedCatalogList[$item->getCustomerGroupId()];
            }
        }

        return $productItems;
    }

    /**
     * Retrieve SharedCatalog list
     *
     * @return array
     */
    private function getSharedCatalogList()
    {
        $sharedCatalogs = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $items = $this->sharedCatalogRepository->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            $sharedCatalogs[$item->getCustomerGroupId()] = $item->getId();
        }
        return $sharedCatalogs;
    }
}
