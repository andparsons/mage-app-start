<?php
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Breadcrumbs data provider
 */
class Breadcrumbs
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param string $categoryPath
     * @return array
     */
    public function getData(string $categoryPath): array
    {
        $breadcrumbsData = [];

        $pathCategoryIds = explode('/', $categoryPath);
        $parentCategoryIds = array_slice($pathCategoryIds, 2, -1);

        if (count($parentCategoryIds)) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect(['name', 'url_key']);
            $collection->addAttributeToFilter('entity_id', $parentCategoryIds);

            foreach ($collection as $category) {
                $breadcrumbsData[] = [
                    'category_id' => $category->getId(),
                    'category_name' => $category->getName(),
                    'category_level' => $category->getLevel(),
                    'category_url_key' => $category->getUrlKey(),
                ];
            }
        }
        return $breadcrumbsData;
    }
}
