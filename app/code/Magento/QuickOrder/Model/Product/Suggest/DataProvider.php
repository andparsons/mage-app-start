<?php
namespace Magento\QuickOrder\Model\Product\Suggest;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provides suggestions for a user during search phrase typing.
 */
class DataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\QuickOrder\Model\FulltextSearch
     */
    private $fulltextSearch;

    /**
     * @var \Magento\QuickOrder\Model\ResourceModel\Product\Suggest
     */
    private $suggestResource;

    /**
     * @var int
     */
    private $resultLimit;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\QuickOrder\Model\FulltextSearch $fulltextSearch
     * @param \Magento\QuickOrder\Model\ResourceModel\Product\Suggest $suggestResource
     * @param int $resultLimit [optional]
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\QuickOrder\Model\FulltextSearch $fulltextSearch,
        \Magento\QuickOrder\Model\ResourceModel\Product\Suggest $suggestResource,
        $resultLimit = 10
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fulltextSearch = $fulltextSearch;
        $this->suggestResource = $suggestResource;
        $this->resultLimit = $resultLimit;
    }

    /**
     * Get search result items for auto-suggest functionality.
     *
     * @param string $query
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItems($query)
    {
        $suggestItems = [];
        $page = 0;

        while (count($suggestItems) < $this->resultLimit) {
            $fulltextSearchResults = $this->fulltextSearch->search($query, $page);
            if (count($fulltextSearchResults)) {
                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->collectionFactory->create();
                $productCollection = $this->suggestResource->prepareProductCollection(
                    $productCollection,
                    $fulltextSearchResults,
                    $this->resultLimit,
                    $query
                );
                $productCollection->load();
                $items = $productCollection->getItems();
                $suggestItems += array_map(function (ProductInterface $item) {
                    $sku = $item->getSku();
                    $name = $item->getName();
                    return [
                        'id' => $sku,
                        'labelSku' => $sku,
                        'labelProductName' => $name,
                        'value' => $sku
                    ];
                }, $items);
                $page++;
            } else {
                break;
            }
        }
        $suggestItems = array_values($suggestItems);

        $suggestItems = $this->sortItems($suggestItems, ['labelProductName', 'id'], $query);
        $suggestItems = $this->orderItemsByExactMatch($suggestItems, ['labelProductName', 'id'], $query);

        return $suggestItems;
    }

    /**
     * Sort suggested items in such way that items which starts with search query will be displayed first.
     *
     * @param array $suggestItems
     * @param array $fieldsToSort
     * @param string $query
     * @return array
     */
    private function sortItems(array $suggestItems, array $fieldsToSort, $query)
    {
        foreach ($fieldsToSort as $fieldToSort) {
            foreach ($suggestItems as $key => $suggestItem) {
                if (stripos(strtolower($suggestItem[$fieldToSort]), $query) === 0) {
                    unset($suggestItems[$key]);
                    array_unshift($suggestItems, $suggestItem);
                }
            }
        }

        return $suggestItems;
    }

    /**
     * Order suggested items by exact match.
     * In some situations fulltext search may provide results with equal relevancy value.
     * Here we move item to the beginning of the results if its field value exactly equal to search query.
     *
     * @param array $suggestItems
     * @param array $fieldsToSort
     * @param string $query
     * @return array
     */
    private function orderItemsByExactMatch(array $suggestItems, array $fieldsToSort, $query)
    {
        foreach ($fieldsToSort as $fieldToSort) {
            foreach ($suggestItems as $key => $suggestItem) {
                if ($suggestItem[$fieldToSort] == $query) {
                    unset($suggestItems[$key]);
                    array_unshift($suggestItems, $suggestItem);
                }
            }
        }

        return $suggestItems;
    }
}
