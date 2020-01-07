<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Model\Form\Storage;

/**
 * Shared Catalog Pricing Wizard Storage.
 */
class Wizard
{
    /**#@+
     * Session keys
     */
    const SESSION_KEY_ASSIGNED_PRODUCT_SKUS = 'assigned_product_skus';
    const SESSION_KEY_UNASSIGNED_PRODUCT_SKUS = 'unassigned_product_skus';
    const SESSION_KEY_PRODUCT_TIER_PRICES = 'product_tier_prices';
    const SESSION_KEY_ASSIGNED_CATEGORIES_IDS = 'assigned_categories_ids';
    const SESSION_KEY_UNASSIGNED_CATEGORIES_IDS = 'unassigned_categories_ids';
    const SESSION_KEY_SELECTED_STORE_ID = 'store_id';
    /**#@-*/

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $session;

    /**
     * @var string
     */
    private $key;

    /**
     * @param \Magento\Framework\Session\Generic $session
     * @param string $key
     */
    public function __construct(
        \Magento\Framework\Session\Generic $session,
        $key
    ) {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * Get assigned product skus.
     *
     * @return array
     */
    public function getAssignedProductSkus()
    {
        return $this->getSessionData(self::SESSION_KEY_ASSIGNED_PRODUCT_SKUS) ?: [];
    }

    /**
     * Set assigned product skus.
     *
     * @param array $productSkus
     * @return void
     */
    public function setAssignedProductSkus(array $productSkus)
    {
        $this->setSessionData(self::SESSION_KEY_ASSIGNED_PRODUCT_SKUS, array_unique($productSkus));
    }

    /**
     * Get unassigned product skus.
     *
     * @return array
     */
    public function getUnassignedProductSkus()
    {
        $unassignedProductSkus = $this->getSessionData(self::SESSION_KEY_UNASSIGNED_PRODUCT_SKUS) ?: [];
        return array_diff($unassignedProductSkus, $this->getAssignedProductSkus());
    }

    /**
     * Set unassigned product skus.
     *
     * @param array $productSkus
     * @return void
     */
    public function setUnassignedProductSkus(array $productSkus)
    {
        $this->setSessionData(self::SESSION_KEY_UNASSIGNED_PRODUCT_SKUS, array_unique($productSkus));
    }

    /**
     * Get assigned to shared catalog categories IDs from session.
     *
     * @return array
     */
    public function getAssignedCategoriesIds()
    {
        return $this->getSessionData(self::SESSION_KEY_ASSIGNED_CATEGORIES_IDS) ?: [];
    }

    /**
     * Set assigned to shared catalog categories IDs to session.
     *
     * @param array $categoriesIds
     * @return void
     */
    private function setAssignedCategoriesIds(array $categoriesIds)
    {
        $this->setSessionData(self::SESSION_KEY_ASSIGNED_CATEGORIES_IDS, array_unique($categoriesIds));
    }

    /**
     * Get unassigned from shared catalog categories IDs from session.
     *
     * @return array
     */
    public function getUnassignedCategoriesIds()
    {
        $unassignedCategoriesIds = $this->getSessionData(self::SESSION_KEY_UNASSIGNED_CATEGORIES_IDS) ?: [];
        return array_diff($unassignedCategoriesIds, $this->getAssignedCategoriesIds());
    }

    /**
     * Set unassigned from shared catalog categories IDs to session.
     *
     * @param array $categoriesIds
     * @return void
     */
    private function setUnassignedCategoriesIds(array $categoriesIds)
    {
        $this->setSessionData(self::SESSION_KEY_UNASSIGNED_CATEGORIES_IDS, array_unique($categoriesIds));
    }

    /**
     * Set selected shared catalog store ID to session.
     *
     * @param int $id
     * @return void
     */
    public function setStoreId($id)
    {
        $this->session->setData(self::SESSION_KEY_SELECTED_STORE_ID, $id);
    }

    /**
     * Get selected shared catalog store ID from session.
     *
     * @return int
     */
    public function getStoreId()
    {
        return (int)$this->session->getData(self::SESSION_KEY_SELECTED_STORE_ID);
    }

    /**
     * Assign products.
     *
     * @param array $productSkus
     * @return void
     */
    public function assignProducts(array $productSkus)
    {
        $assignedProductSkus = array_merge($this->getAssignedProductSkus(), $productSkus);
        $this->setAssignedProductSkus($assignedProductSkus);
    }

    /**
     * Unassign products.
     *
     * @param array $productSkus
     * @return void
     */
    public function unassignProducts(array $productSkus)
    {
        $unassignedProductSkus = array_merge($this->getUnassignedProductSkus(), $productSkus);
        $this->setUnassignedProductSkus($unassignedProductSkus);
        $this->setAssignedProductSkus(array_diff($this->getAssignedProductSkus(), $productSkus));
    }

    /**
     * Add provided shared catalog assigned categories IDs to session.
     *
     * @param array $categoriesIds
     * @return void
     */
    public function assignCategories(array $categoriesIds)
    {
        $assignedCategoriesIds = array_merge($this->getAssignedCategoriesIds(), $categoriesIds);
        $this->setAssignedCategoriesIds($assignedCategoriesIds);
    }

    /**
     * Add provided shared catalog unassigned categories IDs to session.
     * Thereafter correct already assigned categories IDs.
     *
     * @param array $categoriesIds
     * @return void
     */
    public function unassignCategories(array $categoriesIds)
    {
        $unassignedCategoriesIds = array_merge($this->getUnassignedCategoriesIds(), $categoriesIds);
        $this->setUnassignedCategoriesIds($unassignedCategoriesIds);
        $this->setAssignedCategoriesIds(array_diff($this->getAssignedCategoriesIds(), $categoriesIds));
    }

    /**
     * Check if product is assigned to the shared catalog.
     *
     * @param string $productSku
     * @return bool
     */
    public function isProductAssigned($productSku)
    {
        return in_array($productSku, $this->getAssignedProductSkus());
    }

    /**
     * Mark tier price as deleted by product SKU, qty and website.
     *
     * @param string $productSku
     * @param float $qty
     * @param int $websiteId [optional]
     * @return void
     */
    public function deleteTierPrice($productSku, $qty, $websiteId = 0)
    {
        $productPrices = $this->getTierPrices($productSku, true);
        foreach ($productPrices as &$item) {
            if ($item['qty'] == $qty || $item['website_id'] == $websiteId) {
                $item['is_changed'] = true;
                $item['is_deleted'] = true;
            }
        }
        $this->setProductTierPrices($productPrices, $productSku);
    }

    /**
     * Mark all tier prices for specified product as deleted.
     *
     * @param string $productSku
     * @return void
     */
    public function deleteTierPrices($productSku)
    {
        $productPrices = $this->getTierPrices($productSku, true);
        foreach ($productPrices as &$item) {
            $item['is_changed'] = true;
            $item['is_deleted'] = true;
        }
        $this->setProductTierPrices($productPrices, $productSku);
    }

    /**
     * Get tier prices from storage by product SKU.
     *
     * @param string|null $productSku [optional] If not provided the method returns tier prices for all products
     * @param bool $joinDeleted [optional]
     * @return array
     */
    public function getTierPrices($productSku = null, $joinDeleted = false)
    {
        $data = $this->getSessionData(self::SESSION_KEY_PRODUCT_TIER_PRICES) ?: [];
        if ($productSku) {
            $data = isset($data[$productSku]) ? $data[$productSku] : [];
            if (!$joinDeleted) {
                $data = $this->getPresentPrices($data);
            }
        } else {
            if (!$joinDeleted) {
                foreach ($data as $key => $productPrices) {
                    $data[$key] = $this->getPresentPrices($productPrices);
                }
            }
        }
        return $data;
    }

    /**
     * Return price array without deleted prices.
     *
     * @param array $data
     * @return array
     */
    private function getPresentPrices(array $data)
    {
        foreach ($data as $key => $price) {
            if (!empty($price['is_changed']) && !empty($price['is_deleted'])) {
                unset($data[$key]);
            }
        }
        return array_values($data);
    }

    /**
     * Set products prices.
     *
     * @param array $tierPrices
     * @param string $productSku
     * @return void
     */
    private function setProductTierPrices(array $tierPrices, $productSku)
    {
        $data = $this->getTierPrices(null, true);
        if ($productSku) {
            $data[$productSku] = $tierPrices;
        }
        $this->setSessionData(self::SESSION_KEY_PRODUCT_TIER_PRICES, $data);
    }

    /**
     * Set tier prices for products.
     *
     * @param array $tierPrices
     * @return void
     */
    public function setTierPrices(array $tierPrices)
    {
        $data = $this->getSessionData(self::SESSION_KEY_PRODUCT_TIER_PRICES) ?: [];
        foreach ($tierPrices as $sku => $productPrices) {
            if (isset($data[$sku])) {
                $productPrices = $this->mergeProductPrices($data[$sku], $productPrices);
            }
            $data[$sku] = $productPrices;
        }
        $this->setSessionData(self::SESSION_KEY_PRODUCT_TIER_PRICES, $data);
    }

    /**
     * Add new product price to the existing ones if price with the same qty and website doesn't exist.
     * Otherwise replace existing price with new.
     *
     * @param array $existingItems
     * @param array $newItems
     * @return array
     */
    private function mergeProductPrices(array $existingItems, array $newItems)
    {
        foreach ($newItems as $newItem) {
            $present = false;
            foreach ($existingItems as $key => $existingItem) {
                if ($existingItem['qty'] == $newItem['qty'] && $existingItem['website_id'] == $newItem['website_id']) {
                    $existingItems[$key] = $newItem;
                    $present = true;
                    break;
                }
            }
            if (!$present) {
                $existingItems[] = $newItem;
            }
        }
        return $existingItems;
    }

    /**
     * Get product prices with qty=1 for website with id $websiteId.
     *
     * @param string $productSku
     * @param int $websiteId [optional]
     * @return array|null
     */
    public function getProductPrice($productSku, $websiteId = 0)
    {
        $productPrice = null;

        foreach ($this->getTierPrices($productSku) as $item) {
            if ($item['qty'] == 1 && $item['website_id'] == $websiteId) {
                $productPrice = $item;
                break;
            }
        }

        if ($productPrice === null && $websiteId != 0) {
            $productPrice = $this->getProductPrice($productSku, 0);
        }

        return $productPrice;
    }

    /**
     * Get product prices with qty=1 for all websites.
     *
     * @param string $productSku
     * @return array
     */
    public function getProductPrices($productSku)
    {
        $productPrices = [];
        foreach ($this->getTierPrices($productSku) as $item) {
            if ($item['qty'] == 1) {
                $productPrices[$item['website_id']] = $item;
            }
        }
        return $productPrices;
    }

    /**
     * Get session data.
     *
     * @param string $paramKey
     * @return array
     */
    private function getSessionData($paramKey)
    {
        return $this->session->getData($this->getParamSessionKey($paramKey));
    }

    /**
     * Set session data.
     *
     * @param string $paramKey
     * @param array $value
     * @return void
     */
    private function setSessionData($paramKey, array $value)
    {
        $this->session->setData(
            $this->getParamSessionKey($paramKey),
            $value
        );
    }

    /**
     * Get session key for param.
     *
     * @param string $paramKey
     * @return string
     */
    private function getParamSessionKey($paramKey)
    {
        return sprintf('%s_%s', $this->key, $paramKey);
    }
}
