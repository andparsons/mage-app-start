<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\ProductItemRepositoryInterface;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem\CollectionFactory as ProductItemCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * Repository for ProductItem model.
 */
class ProductItemRepository implements ProductItemRepositoryInterface
{
    const EQUAL_VALUE = 'eq';

    /**
     * @var \Magento\SharedCatalog\Api\Data\ProductItemInterface[]
     */
    private $instances = [];

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemFactory
     */
    private $sharedCatalogProductItemFactory;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem
     */
    private $sharedCatalogProductItemResource;

    /**
     * @var ProductItemCollectionFactory
     */
    private $sharedCatalogProductItemCollectionFactory;

    /**
     * @var \Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \Magento\SharedCatalog\Model\ProductItemFactory $sharedCatalogProductItemFactory
     * @param \Magento\SharedCatalog\Model\ResourceModel\ProductItem $sharedCatalogProductItemResource
     * @param ProductItemCollectionFactory $sharedCatalogProductItemCollectionFactory
     * @param \Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Magento\SharedCatalog\Model\ProductItemFactory $sharedCatalogProductItemFactory,
        \Magento\SharedCatalog\Model\ResourceModel\ProductItem $sharedCatalogProductItemResource,
        ProductItemCollectionFactory $sharedCatalogProductItemCollectionFactory,
        \Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->sharedCatalogProductItemFactory = $sharedCatalogProductItemFactory;
        $this->sharedCatalogProductItemResource = $sharedCatalogProductItemResource;
        $this->sharedCatalogProductItemCollectionFactory = $sharedCatalogProductItemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(ProductItemInterface $sharedCatalogProductItem)
    {
        try {
            $this->validate($sharedCatalogProductItem);
            $this->sharedCatalogProductItemResource->save($sharedCatalogProductItem);
        } catch (InputException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save ProductItem'),
                $e
            );
        }
        unset($this->instances[$sharedCatalogProductItem->getId()]);
        return $sharedCatalogProductItem->getId();
    }

    /**
     * @inheritdoc
     */
    public function get($sharedCatalogProductItemId)
    {
        if (!isset($this->instances[$sharedCatalogProductItemId])) {
            /** @var ProductItemInterface $sharedCatalogProductItem */
            $sharedCatalogProductItem = $this->sharedCatalogProductItemFactory->create();
            $sharedCatalogProductItem->load($sharedCatalogProductItemId);
            if (!$sharedCatalogProductItem->getId()) {
                throw NoSuchEntityException::singleField('id', $sharedCatalogProductItemId);
            }
            $this->instances[$sharedCatalogProductItemId] = $sharedCatalogProductItem;
        }
        return $this->instances[$sharedCatalogProductItemId];
    }

    /**
     * @inheritdoc
     */
    public function delete(ProductItemInterface $sharedCatalogProductItem)
    {
        try {
            $sharedCatalogProductItemId = $sharedCatalogProductItem->getId();
            $this->sharedCatalogProductItemResource->delete($sharedCatalogProductItem);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete product with id %1',
                    $sharedCatalogProductItem->getId()
                ),
                $e
            );
        }
        unset($this->instances[$sharedCatalogProductItemId]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($sharedCatalogProductItemId)
    {
        $sharedCatalogProductItem = $this->get($sharedCatalogProductItemId);
        return $this->delete($sharedCatalogProductItem);
    }

    /**
     * @inheritdoc
     */
    public function deleteItems(array $sharedCatalogProductItems)
    {
        $skusByGroupId = [];
        foreach ($sharedCatalogProductItems as $productItem) {
            $skusByGroupId[$productItem->getCustomerGroupId()][] = $productItem->getSku();
            unset($this->instances[$productItem->getId()]);
        }
        foreach ($skusByGroupId as $customerGroupId => $productSkus) {
            $this->sharedCatalogProductItemResource->deleteItems($productSkus, $customerGroupId);
        }

        return true;
    }

    /**
     * Check that product item is populated properly.
     *
     * @param ProductItemInterface $sharedCatalogProductItem
     * @throws InputException
     * @return void
     */
    private function validate(ProductItemInterface $sharedCatalogProductItem)
    {
        $exception = new InputException();
        if (empty($sharedCatalogProductItem->getSku())) {
            $exception->addError(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['fieldName' => 'sku', 'value' => $sharedCatalogProductItem->getSku()]
                )
            );
        }
        if ($sharedCatalogProductItem->getCustomerGroupId() === null) {
            $exception->addError(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['fieldName' => 'customer_group_id', 'value' => $sharedCatalogProductItem->getCustomerGroupId()]
                )
            );
        }
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Collection $collection */
        $collection = $this->sharedCatalogProductItemCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }
}
