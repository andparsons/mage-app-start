<?php

namespace Magento\SharedCatalog\Model\Indexer;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\UpdateIndexInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\MultiDimensionProvider;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactoryFactory;

/**
 * Copy index data in the table from default customer group
 */
class CopyPriceIndex implements UpdateIndexInterface
{
    /**
     * @var CopyIndex
     */
    private $copyIndex;

    /**
     * @var IndexScopeResolverInterface
     */
    private $indexScopeResolver;

    /**
     * @var WebsiteDimensionProvider
     */
    private $websiteDimensionProvider;

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var DimensionCollectionFactoryFactory
     */
    private $dimensionCollectionFactoryFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Indexer\CustomerGroupDataProvider
     */
    private $customerGroupDataProvider;

    /**
     * Constructor
     * @param CopyIndex $copyIndex
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param WebsiteDimensionProvider $websiteDimensionProvider
     * @param DimensionFactory $dimensionFactory
     * @param GroupManagementInterface $groupManagement
     * @param DimensionCollectionFactoryFactory $dimensionCollectionFactoryFactory
     * @param \Magento\SharedCatalog\Model\Indexer\CustomerGroupDataProvider $customerGroupDataProvider
     */
    public function __construct(
        CopyIndex $copyIndex,
        IndexScopeResolverInterface $indexScopeResolver,
        WebsiteDimensionProvider $websiteDimensionProvider,
        GroupManagementInterface $groupManagement,
        DimensionCollectionFactoryFactory $dimensionCollectionFactoryFactory,
        \Magento\SharedCatalog\Model\Indexer\CustomerGroupDataProviderFactory $customerGroupDataProvider
    ) {
        $this->copyIndex = $copyIndex;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->websiteDimensionProvider = $websiteDimensionProvider;
        $this->groupManagement = $groupManagement;
        $this->dimensionCollectionFactoryFactory = $dimensionCollectionFactoryFactory;
        $this->customerGroupDataProvider = $customerGroupDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function update(GroupInterface $group, $isGroupNew)
    {
        if (!$isGroupNew) {
            return;
        }
        $target = $this->getTables($group);
        $source = $this->getTables($this->groupManagement->getDefaultGroup());
        $this->copyIndex->copy($group, $target, $source);
    }

    /**
     * @param GroupInterface $group
     * @return array
     */
    private function getTables(GroupInterface $group)
    {
        $tables = [];
        foreach ($this->getAffectedDimensions($group) as $dimensions) {
            $tables[] = $this->indexScopeResolver->resolve('catalog_product_index_price', $dimensions);
        }

        return $tables;
    }

    /**
     * Get affected dimensions
     * @param GroupInterface $group
     * @return MultiDimensionProvider
     */
    private function getAffectedDimensions(GroupInterface $group)
    {
        /** @var DimensionCollectionFactory $source */
        $source = $this->dimensionCollectionFactoryFactory->create(
            [
                'dimensionProviders' => [
                    'websites' => $this->websiteDimensionProvider,
                    'customer_groups' => $this->customerGroupDataProvider->create(
                        [
                            'customerGroup' => $group
                        ]
                    )
                ]
            ]
        );
        return $source->create();
    }
}
