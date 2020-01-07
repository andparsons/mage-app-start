<?php
namespace Magento\SharedCatalog\Observer\Controller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SharedCatalog\Api\CategoryManagementInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\ProductManagementInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\SharedCatalog\Model\ProductSharedCatalogsLoader;

/**
 * Add product to the selected shared catalogs after saving.
 */
class SaveProduct implements ObserverInterface
{
    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface
     */
    private $productSharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductSharedCatalogsLoader
     */
    private $productSharedCatalogsLoader;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryManagementInterface
     */
    private $categoryManagement;

    /**
     * @param ProductManagementInterface $productSharedCatalogManagement
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductSharedCatalogsLoader $productSharedCatalogsLoader
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryManagementInterface $categoryManagement
     */
    public function __construct(
        ProductManagementInterface $productSharedCatalogManagement,
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductSharedCatalogsLoader $productSharedCatalogsLoader,
        CategoryRepositoryInterface $categoryRepository,
        CategoryManagementInterface $categoryManagement
    ) {
        $this->productSharedCatalogManagement = $productSharedCatalogManagement;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productSharedCatalogsLoader = $productSharedCatalogsLoader;
        $this->categoryRepository = $categoryRepository;
        $this->categoryManagement = $categoryManagement;
    }

    /**
     * Add product to the selected shared catalogs after saving.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();

        $categories = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $categories[] = $this->categoryRepository->get($categoryId);
        }

        $customerGroupIds = $this->retrieveCustomerGroupIds((array)$product->getData('tier_price'));
        $sharedCatalogIds = $this->prepareSharedCatalogIds(
            (array)$product->getData('shared_catalog'),
            (array)$customerGroupIds
        );
        $assignedSharedCatalogs = $this->productSharedCatalogsLoader->getAssignedSharedCatalogs($product->getSku());
        $assignedSharedCatalogIds = array_keys($assignedSharedCatalogs);

        $forCreate = array_diff($sharedCatalogIds, $assignedSharedCatalogIds);
        foreach ($forCreate as $sharedCatalogId) {
            $this->categoryManagement->assignCategories($sharedCatalogId, $categories);
            $this->productSharedCatalogManagement->assignProducts($sharedCatalogId, [$product]);
        }

        $forDelete = array_diff($assignedSharedCatalogIds, $sharedCatalogIds);
        foreach ($forDelete as $sharedCatalogId) {
            $this->productSharedCatalogManagement->unassignProducts($sharedCatalogId, [$product]);
        }
    }

    /**
     * Prepare list of shared catalog ids.
     *
     * @param array $sharedCatalogsIds
     * @param array $customerGroupIds
     * @return array
     */
    private function prepareSharedCatalogIds(array $sharedCatalogsIds, array $customerGroupIds): array
    {
        if ($customerGroupIds) {
            $this->searchCriteriaBuilder->addFilter(
                SharedCatalogInterface::CUSTOMER_GROUP_ID,
                $customerGroupIds,
                'in'
            );
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria)->getItems();

            foreach ($sharedCatalogs as $sharedCatalog) {
                if (!in_array($sharedCatalog->getId(), $sharedCatalogsIds)) {
                    $sharedCatalogsIds[] = $sharedCatalog->getId();
                }
            }
        }

        return $sharedCatalogsIds;
    }

    /**
     * Retrieve customer group ids list from tier prices data.
     *
     * @param array $tierPricesData
     * @return array
     */
    private function retrieveCustomerGroupIds(array $tierPricesData): array
    {
        $customerGroups = [];

        foreach ($tierPricesData as $tierPrice) {
            if (!isset($tierPrice['delete']) && !empty($tierPrice['cust_group'])) {
                $customerGroups[] = $tierPrice['cust_group'];
            }
        }

        return $customerGroups;
    }
}
