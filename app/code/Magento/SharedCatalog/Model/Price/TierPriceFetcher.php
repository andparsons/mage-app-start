<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Model\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Price\TierPriceFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice as TierPriceResource;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for fetching tier prices for shared catalog.
 */
class TierPriceFetcher
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var TierPriceResource
     */
    private $tierPriceResource;

    /**
     * @var TierPriceFactory
     */
    private $tierPriceFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param MetadataPool $metadataPool
     * @param TierPriceResource $tierPriceResource
     * @param TierPriceFactory $tierPriceFactory
     * @param StoreManagerInterface $storeManager
     * @param int $batchSize
     */
    public function __construct(
        MetadataPool $metadataPool,
        TierPriceResource $tierPriceResource,
        TierPriceFactory $tierPriceFactory,
        StoreManagerInterface $storeManager,
        int $batchSize = 1000
    ) {
        $this->metadataPool = $metadataPool;
        $this->tierPriceResource = $tierPriceResource;
        $this->tierPriceFactory = $tierPriceFactory;
        $this->storeManager = $storeManager;
        $this->batchSize = $batchSize;
    }

    /**
     * Fetch tier prices associated with shared catalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param array $skus
     * @return \Traversable
     */
    public function fetch(SharedCatalogInterface $sharedCatalog, array $skus): \Traversable
    {
        $connection = $this->tierPriceResource->getConnection();

        $selectPrototype = $connection->select();
        $selectPrototype->from(
            ['tp' => $this->tierPriceResource->getMainTable()]
        );
        $selectPrototype->where('tp.customer_group_id = ?', $sharedCatalog->getCustomerGroupId());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)
            ->getLinkField();
        $selectPrototype->joinInner(
            ['p' => $this->tierPriceResource->getTable('catalog_product_entity')],
            'tp.' . $linkField . ' = p.' . $linkField,
            ['sku']
        );
        $sharedCatalogWebsiteId = $this->retrieveWebsiteId($sharedCatalog);
        if (Store::DEFAULT_STORE_ID !== $sharedCatalogWebsiteId) {
            $selectPrototype->where('tp.website_id IN (?)', [Store::DEFAULT_STORE_ID, $sharedCatalogWebsiteId]);
        }

        $offset = 0;
        while ($skuBatch = \array_slice($skus, $offset, $this->batchSize)) {
            $select = clone $selectPrototype;
            $select->where('p.sku IN (?)', $skuBatch);

            $rows = $connection->fetchAll($select);
            foreach ($rows as $row) {
                yield $this->tierPriceFactory->create($row, $row['sku']);
            }

            $offset += $this->batchSize;
        }
    }

    /**
     * Retrieve website id from shared catalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return int
     */
    private function retrieveWebsiteId(SharedCatalogInterface $sharedCatalog): int
    {
        return $sharedCatalog->getStoreId()
            ? (int) $this->storeManager->getStore($sharedCatalog->getStoreId())->getWebsiteId()
            : Store::DEFAULT_STORE_ID;
    }
}
