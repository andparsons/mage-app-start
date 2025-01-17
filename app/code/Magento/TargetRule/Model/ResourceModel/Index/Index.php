<?php
namespace Magento\TargetRule\Model\ResourceModel\Index;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Store;
use Magento\TargetRule\Model\Cache\Index as IndexCache;
use Magento\TargetRule\Model\Index as IndexModel;

class Index implements IndexInterface
{
    const CACHE_TAG_TYPE_ENTITY = 'entity';
    const CACHE_TAG_TYPE_STORE = 'store';
    const CACHE_TAG_TYPE_CUSTOMER_GROUP = 'customer_group';
    const CACHE_TAG_TYPE_CUSTOMER_SEGMENT = 'customer_segment';

    /**
     * @var IndexCache
     */
    private $cache;

    /**
     * @var string
     */
    private $type;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param IndexCache $cache
     * @param string $type
     * @param SerializerInterface|null $serializer
     */
    public function __construct(IndexCache $cache, $type, SerializerInterface $serializer = null)
    {
        $this->cache = $cache;
        $this->type = $type;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function loadProductIdsBySegmentId(IndexModel $indexModel, $segmentId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $indexModel->getProduct();
        $storeId = $indexModel->getStoreId();
        $customerGroupId = $indexModel->getCustomerGroupId();

        $entityId = $product->getEntityId();
        $customerSegmentId = $segmentId;

        $cacheKey = $this->generateKey($entityId, $storeId, $customerGroupId, $customerSegmentId);

        $result = $this->cache->load($cacheKey);

        if (is_string($result) && !empty($result)) {
            $result = $this->serializer->unserialize($result);
        }

        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function saveResultForCustomerSegments(IndexModel $indexModel, $segmentId, array $productIds)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $indexModel->getProduct();

        $entityId = $product->getEntityId();
        $storeId = $indexModel->getStoreId();
        $customerGroupId = $indexModel->getCustomerGroupId();
        $customerSegmentId = $segmentId;

        $cacheKey = $this->generateKey($entityId, $storeId, $customerGroupId, $customerSegmentId);

        $productTags = $this->generateTagsByProductIds($productIds);

        $keyTags = $this->getKeyTags($entityId, $storeId, $customerGroupId, $customerSegmentId);

        $tags = array_merge($productTags, $keyTags);

        if (!empty($productIds)) {
            $productIds = $this->serializer->serialize($productIds);
            if (!$this->cache->save($productIds, $cacheKey, $tags)) {
                $this->cache->remove($cacheKey);
            }
        } else {
            $this->cache->remove($cacheKey);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($store = null)
    {
        if ($store === null) {
            $this->cleanAll();
        } else {
            if ($store instanceof Store) {
                $store = $store->getId();
            } elseif (is_array($store)) {
                $store = current($store);
            }

            $tagName = $this->getSpecificTagName(self::CACHE_TAG_TYPE_STORE, $store);
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$tagName]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteProductFromIndex($entityId = null)
    {
        if ($entityId !== null) {
            $mainTagName = $this->getSpecificTagName(self::CACHE_TAG_TYPE_ENTITY, $entityId);
            $productTagName = $this->getProductTagName($entityId);
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$mainTagName, $productTagName]);
        }

        return $this;
    }

    /**
     * Clean index
     *
     * @return $this
     */
    private function cleanAll()
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_ALL);

        return $this;
    }

    /**
     * @param string $suffix
     * @return string
     */
    private function getMainTagName($suffix)
    {
        return $this->getMainPrefix() . "main_{$suffix}";
    }

    /**
     * @param string $suffix
     * @return string
     */
    private function getProductTagName($suffix)
    {
        return $this->getMainPrefix() . "product_{$suffix}";
    }

    /**
     * @param string $type
     * @param string $suffix
     * @return string
     */
    private function getSpecificTagName($type, $suffix)
    {
        return $this->getMainTagName("{$type}_{$suffix}");
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @param int $customerGroupId
     * @param int $customerSegmentId
     * @return string
     */
    private function generateKey($entityId, $storeId, $customerGroupId, $customerSegmentId)
    {
        return $this->getMainPrefix() . "{$entityId}_{$storeId}_{$customerGroupId}_{$customerSegmentId}";
    }

    /**
     * @return string
     */
    private function getMainPrefix()
    {
        return $this->cache->getTag() . "_{$this->type}_";
    }

    /**
     * @param int[] $productIds
     * @return string[]
     */
    private function generateTagsByProductIds(array $productIds)
    {
        $tags = [];
        foreach ($productIds as $productId) {
            $tags[] = $this->getProductTagName($productId);
        }

        return $tags;
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @param int $customerGroupId
     * @param int $customerSegmentId
     * @return string[]
     */
    private function getKeyTags($entityId, $storeId, $customerGroupId, $customerSegmentId)
    {
        $entityIdTagName = $this->getSpecificTagName(self::CACHE_TAG_TYPE_ENTITY, $entityId);
        $storeIdTagName = $this->getSpecificTagName(self::CACHE_TAG_TYPE_STORE, $storeId);
        $customerGroupIdTagName = $this->getSpecificTagName(self::CACHE_TAG_TYPE_CUSTOMER_GROUP, $customerGroupId);
        $customerSegmentIdTagName = $this->getSpecificTagName(
            self::CACHE_TAG_TYPE_CUSTOMER_SEGMENT,
            $customerSegmentId
        );

        return [$entityIdTagName, $storeIdTagName, $customerGroupIdTagName, $customerSegmentIdTagName];
    }
}
