<?php
namespace Magento\QuickOrder\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;

/**
 * Prepare product collection for suggestions functionality.
 */
class Suggest
{
    /**
     * @var \Magento\QuickOrder\Model\CatalogPermissions\Permissions
     */
    private $permissions;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $tempStorageFactory;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    private $dbHelper;

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * @param \Magento\QuickOrder\Model\CatalogPermissions\Permissions $permissions
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $tempStorageFactory
     * @param \Magento\Framework\DB\Helper $dbHelper
     * @param Visibility|null $catalogProductVisibility
     */
    public function __construct(
        \Magento\QuickOrder\Model\CatalogPermissions\Permissions $permissions,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $tempStorageFactory,
        \Magento\Framework\DB\Helper $dbHelper,
        Visibility $catalogProductVisibility = null
    ) {
        $this->permissions = $permissions;
        $this->tempStorageFactory = $tempStorageFactory;
        $this->dbHelper = $dbHelper;
        $this->catalogProductVisibility = $catalogProductVisibility
            ?? ObjectManager::getInstance()->get(Visibility::class);
    }

    /**
     * Prepare product collection select.
     *
     * Here we prepare products collection to be ready for usage by applying following actions:
     * - inner join Fulltext Search (Elastic Search) results to the collection. It allows us to reduce the
     * collection size significantly;
     * - apply category permissions to the collection;
     * - set collection size and sort order;
     * - add required attributes to the collection;
     * - exclude hidden products with required custom options from the collection.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $fulltextSearchResults
     * @param int $resultLimit
     * @param string $query
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Zend_Db_Exception
     */
    public function prepareProductCollection(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        $fulltextSearchResults,
        $resultLimit,
        $query
    ) {
        $productCollection->addAttributeToSelect(ProductInterface::NAME);
        $tempStorage = $this->tempStorageFactory->create();
        $table = $tempStorage->storeApiDocuments($fulltextSearchResults);
        $productCollection->getSelect()->joinInner(
            [
                'search_result' => $table->getName(),
            ],
            'e.entity_id = search_result.' . \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::FIELD_ENTITY_ID,
            []
        );
        $this->permissions->applyPermissionsToProductCollection($productCollection);
        $productCollection->setPageSize($resultLimit)->getSelect()->order('search_result.score DESC');

        $query = $this->dbHelper->escapeLikeValue($query, ['position' => 'any']);
        $productCollection->addAttributeToFilter([
            ['attribute' => ProductInterface::SKU, 'like' => $query],
            ['attribute' => ProductInterface::NAME, 'like' => $query],
        ]);

        // here we exclude from collection hidden in catalog products with required custom options.
        $productCollection->setVisibility($this->catalogProductVisibility->getVisibleInSearchIds());

        return $productCollection;
    }
}
