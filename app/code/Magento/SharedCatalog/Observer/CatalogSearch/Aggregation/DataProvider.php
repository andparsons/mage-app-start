<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Observer\CatalogSearch\Aggregation;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\SharedCatalog\Api\StatusInfoInterface;
use Magento\Framework\DB\Select;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem;
use Magento\Store\Model\ScopeInterface;

/**
 * Class changes filter attribute logic for shared catalog.
 */
class DataProvider implements ObserverInterface
{
    /**
     * @var StatusInfoInterface
     */
    private $statusInfo;

    /**
     * @var ProductItem
     */
    private $productItem;

    /**
     * @var Product
     */
    private $product;

    /**
     * @param StatusInfoInterface $statusInfo
     * @param ProductItem $productItem
     * @param Product $product
     */
    public function __construct(
        StatusInfoInterface $statusInfo,
        ProductItem $productItem,
        Product $product
    ) {
        $this->statusInfo = $statusInfo;
        $this->productItem = $productItem;
        $this->product = $product;
    }

    /**
     * Join shared catalog item.
     *
     * @param $observer $observer
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $bucket = $observer->getEvent()->getBucket();
        /** @var \Magento\Framework\Db\Select $select */
        $select = $observer->getEvent()->getSelect();

        if (!$this->statusInfo->isActive(ScopeInterface::SCOPE_STORE, null)
            || $bucket->getName() == 'price_bucket') {
            return $select;
        }

        $select->joinInner(
            ['product_entity' => $this->product->getEntityTable()],
            'main_table.source_id  = product_entity.entity_id',
            ['sku']
        );
        $select->joinInner(
            ['shared_catalog_item' => $this->productItem->getMainTable()],
            'product_entity.sku  = shared_catalog_item.sku',
            []
        );

        return $select;
    }
}
