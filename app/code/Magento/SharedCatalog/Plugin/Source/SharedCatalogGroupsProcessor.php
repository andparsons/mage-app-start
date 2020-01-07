<?php

namespace Magento\SharedCatalog\Plugin\Source;

use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;

/**
 * Update options list of the customer group dropdown.
 */
class SharedCatalogGroupsProcessor
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaFactory
     */
    private $searchCriteriaFactory;

    /**
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param SearchCriteriaFactory $searchCriteriaFactory
     */
    public function __construct(
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        SearchCriteriaFactory $searchCriteriaFactory
    ) {
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
    }

    /**
     * Separate items by two groups: "Customer Group" and "Shared Catalog".
     *
     * @param array $groups
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareGroups(array $groups)
    {
        if (empty($groups)) {
            return $groups;
        }

        /** @var \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $this->searchCriteriaFactory->create();
        $sharedCatalogOptions = [];
        $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria);
        $values = array_column($groups, 'value');
        $customerGroupOptions = array_combine($values, $groups);

        /** @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog */
        foreach ($sharedCatalogs->getItems() as $sharedCatalog) {
            $sharedCatalogGroupId = $sharedCatalog->getCustomerGroupId();

            if ($customerGroupOptions[$sharedCatalogGroupId]['value'] == $sharedCatalogGroupId) {
                unset($customerGroupOptions[$sharedCatalogGroupId]);

                $sharedCatalogOptions[] = [
                    'label' => $sharedCatalog->getName(),
                    'value' => $sharedCatalogGroupId,
                    '__disableTmpl' => true,
                ];
            }
        }

        return [
            [
                'label' => __('Customer Groups'),
                'value' => array_values($customerGroupOptions),
                '__disableTmpl' => true,
            ],
            [
                'label' => __('Shared Catalogs'),
                'value' => $sharedCatalogOptions,
                '__disableTmpl' => true,
            ],
        ];
    }
}
