<?php
namespace Magento\SharedCatalog\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\ProductItemRepositoryInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;

/**
 * Class for load shared catalogs for product.
 */
class ProductSharedCatalogsLoader
{
    /**
     * @var ProductItemRepositoryInterface
     */
    private $linkRepository;

    /**
     * @var SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * ProductSharedCatalogsLoader constructor.
     *
     * @param ProductItemRepositoryInterface $linkRepository
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductItemRepositoryInterface $linkRepository,
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->linkRepository = $linkRepository;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Key is shared catalog id, value is shared catalog object.
     *
     * @param string $sku
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface[]
     */
    public function getAssignedSharedCatalogs($sku)
    {
        $customerGroupIds = $this->getAssignedCustomerGroupIds($sku);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SharedCatalogInterface::CUSTOMER_GROUP_ID, $customerGroupIds, 'in')
            ->create();
        $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria)->getItems();

        $sharedCatalogMap = [];
        foreach ($sharedCatalogs as $sharedCatalog) {
            $sharedCatalogMap[$sharedCatalog->getId()] = $sharedCatalog;
        }
        return $sharedCatalogMap;
    }

    /**
     * Retrieve assigned customer groups.
     *
     * @param string $sku
     * @return array
     */
    private function getAssignedCustomerGroupIds($sku)
    {
        $this->searchCriteriaBuilder->addFilter(ProductItemInterface::SKU, $sku);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $links = $this->linkRepository->getList($searchCriteria)->getItems();

        $customerGroupIds = [];
        foreach ($links as $link) {
            $customerGroupIds[] = $link->getCustomerGroupId();
        }
        return $customerGroupIds;
    }
}
