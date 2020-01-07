<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Model\Customer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SharedCatalog\Api\ProductItemRepositoryInterface;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;

/**
 * Class for checking availability products for customer.
 */
class AvailableProducts
{
    /**
     * @var ProductItemRepositoryInterface
     */
    private $productItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductItemRepositoryInterface $productItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductItemRepositoryInterface $productItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productItemRepository = $productItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Check is product available for customer.
     *
     * @param int $customerGroupId
     * @param string $sku
     * @return bool
     */
    public function isProductAvailable(int $customerGroupId, string $sku): bool
    {
        $searchCriteriaBuilder = clone $this->searchCriteriaBuilder;
        $searchCriteriaBuilder->addFilter(ProductItemInterface::CUSTOMER_GROUP_ID, $customerGroupId);
        $searchCriteriaBuilder->addFilter(ProductItemInterface::SKU, $sku);
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResults = $this->productItemRepository->getList($searchCriteria);

        return (bool) $searchResults->getTotalCount();
    }
}
