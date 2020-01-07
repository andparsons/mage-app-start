<?php
namespace Magento\NegotiableQuoteSharedCatalog\Model;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Retrieves shared catalog data.
 */
class SharedCatalogRetriever
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
     * Check if shared catalog exists.
     *
     * @param int $customerGroupId
     * @return bool
     */
    public function isSharedCatalogPresent($customerGroupId)
    {
        $sharedCatalogExists = false;
        $this->searchCriteriaBuilder->addFilter(SharedCatalogInterface::CUSTOMER_GROUP_ID, $customerGroupId);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria);
        if ($sharedCatalogs->getTotalCount()) {
            $sharedCatalogExists = true;
        }
        return $sharedCatalogExists;
    }

    /**
     * Get Public Shared Catalog.
     *
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPublicCatalog()
    {
        $this->searchCriteriaBuilder->addFilter(SharedCatalogInterface::TYPE, SharedCatalogInterface::TYPE_PUBLIC);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria);

        if (!$sharedCatalogs->getTotalCount()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such public catalog entity'));
        }
        $sharedCatalogItems = $sharedCatalogs->getItems();
        return array_shift($sharedCatalogItems);
    }
}
