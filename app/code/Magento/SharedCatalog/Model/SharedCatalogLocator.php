<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Locator for shared catalog.
 */
class SharedCatalogLocator
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Get Shared Catalog by customer group ID.
     *
     * @param int $customerGroupId
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSharedCatalogByCustomerGroup($customerGroupId)
    {
        $this->searchCriteriaBuilder->addFilter(SharedCatalogInterface::CUSTOMER_GROUP_ID, $customerGroupId);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria);

        if (!$sharedCatalogs->getTotalCount()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such shared catalog entity'));
        }
        $sharedCatalogItems = $sharedCatalogs->getItems();
        return array_shift($sharedCatalogItems);
    }
}
