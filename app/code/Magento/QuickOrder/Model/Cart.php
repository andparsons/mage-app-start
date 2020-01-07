<?php
namespace Magento\QuickOrder\Model;

use Magento\AdvancedCheckout\Model\Cart as AdvancedCheckoutCart;
use Magento\AdvancedCheckout\Helper\Data;
use Magento\AdvancedCheckout\Model\IsProductInStockInterface;

/**
 * Search model for frontend products search.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Cart extends AdvancedCheckoutCart
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\QuickOrder\Model\CatalogPermissions\Permissions
     */
    private $permissions;

    /**
     * List of affected items skus.
     *
     * @var string[]
     */
    private $affectedItems = [];

    /**
     * Default qty for not found SKU
     *
     * @var int
     */
    private $notFoundItemQty = 1;

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Data $checkoutData
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Catalog\Model\Product\CartConfiguration $productConfiguration
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\QuickOrder\Model\CatalogPermissions\Permissions $permissions
     * @param string $itemFailedStatus [optional]
     * @param array $data [optional]
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer [optional]
     * @param \Magento\Framework\Api\SearchCriteriaBuilder|null $searchCriteriaBuilder [optional]
     * @param IsProductInStockInterface $isProductInStock
     *
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Data $checkoutData,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Catalog\Model\Product\CartConfiguration $productConfiguration,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\QuickOrder\Model\CatalogPermissions\Permissions $permissions,
        $itemFailedStatus = Data::ADD_ITEM_STATUS_FAILED_SKU,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder = null,
        IsProductInStockInterface $isProductInStock = null
    ) {
        parent::__construct(
            $cart,
            $messageFactory,
            $eventManager,
            $checkoutData,
            $optionFactory,
            $wishlistFactory,
            $quoteRepository,
            $storeManager,
            $localeFormat,
            $messageManager,
            $productTypeConfig,
            $productConfiguration,
            $customerSession,
            $stockRegistry,
            $stockState,
            $stockHelper,
            $productRepository,
            $quoteFactory,
            $itemFailedStatus,
            $data,
            $serializer,
            $searchCriteriaBuilder,
            $isProductInStock
        );
        $this->priceCurrency = $priceCurrency;
        $this->imageHelper = $imageHelper;
        $this->permissions = $permissions;
    }

    /**
     * Check item before adding by SKU.
     *
     * @param string $sku
     * @param float $qty
     * @param array $config Configuration data of the product (if has been configured) [optional]
     * @return array
     */
    public function checkItem($sku, $qty, $config = [])
    {
        $item = $this->_getValidatedItem($sku, $qty);
        if ($item['code'] == Data::ADD_ITEM_STATUS_FAILED_EMPTY) {
            return $item;
        }

        if (!empty($config)) {
            $this->setAffectedItemConfig($sku, $config);
        }

        /** @var $product \Magento\Catalog\Api\Data\ProductInterface */
        $product = $this->_loadProductWithOptionsBySku($item['sku'], $config);

        if ($product && $product->hasConfiguredOptions()) {
            $config['options'] = $product->getConfiguredOptions();
        }

        if ($product && $product->getId()) {
            $result = $this->checkItemProduct($product, $item, $sku, $config);
            if (count($result)) {
                return $result;
            }
        } else {
            $item['qty'] = $this->notFoundItemQty;
            return $this->_updateItem($item, Data::ADD_ITEM_STATUS_FAILED_SKU);
        }

        return $this->_updateItem($item, Data::ADD_ITEM_STATUS_SUCCESS);
    }

    /**
     * Check product item configuration.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $item
     * @param string $sku
     * @param array $config
     * @return array
     */
    protected function checkItemProduct(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        array $item,
        $sku,
        array $config
    ) {
        $prevalidateStatus = $item['code'];
        unset($item['code']);

        if ($item['qty'] === '') {
            $qty = $this->getProductDefaultQty($product);
            $item = $this->_getValidatedItem($sku, $qty);
            $prevalidateStatus = $item['code'];
            unset($item['code']);
        }

        $item = $this->addExtraData($item, $product);
        $result = $this->validateGeneral($product, $item);
        if (count($result)) {
            return $result;
        }

        if ($this->_shouldBeConfigured($product)) {
            $result = $this->validateConfiguredProduct($product, $item, $config);
            if (count($result)) {
                return $result;
            } else {
                $item['code'] = Data::ADD_ITEM_STATUS_SUCCESS;
            }
        }

        if ($prevalidateStatus != Data::ADD_ITEM_STATUS_SUCCESS) {
            return $this->_updateItem($item, $prevalidateStatus);
        }

        $result = $this->addQtyData($product, $item);
        if (is_array($result) && count($result)) {
            return $result;
        }

        return [];
    }

    /**
     * Check if product is disabled.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    private function isDisabled(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return $this->_isCheckout() && $product->isDisabled();
    }

    /**
     * Check if adding to cart is disabled.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    private function isDisableAddToCart(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return $this->_isFrontend() && (true === $product->getDisableAddToCart());
    }

    /**
     * Check if product is visible in site.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    private function isVisibleInSiteVisibility(\Magento\Catalog\Api\Data\ProductInterface $product): bool
    {
        return $this->_isFrontend() && $product->isVisibleInSiteVisibility();
    }

    /**
     * General validation.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $item
     * @return array
     */
    private function validateGeneral(\Magento\Catalog\Api\Data\ProductInterface $product, array $item)
    {
        if (!$this->permissions->isProductPermissionsValid($product) || !$this->isVisibleInSiteVisibility($product)) {
            $failCode = Data::ADD_ITEM_STATUS_FAILED_SKU;
            return $this->_updateItem($item, $failCode);
        }

        if ($this->isDisabled($product)) {
            $item['is_configure_disabled'] = true;
            $failCode = $this->_context == self::CONTEXT_FRONTEND
                ? Data::ADD_ITEM_STATUS_FAILED_SKU
                : Data::ADD_ITEM_STATUS_FAILED_DISABLED;
            return $this->_updateItem($item, $failCode);
        }

        if ($this->isDisableAddToCart($product)) {
            return $this->_updateItem(
                $item,
                Data::ADD_ITEM_STATUS_FAILED_PERMISSIONS
            );
        }

        $productWebsiteValidationResult = $this->_validateProductWebsite($product);
        if ($productWebsiteValidationResult !== true) {
            $item['is_configure_disabled'] = true;
            return $this->_updateItem($item, $productWebsiteValidationResult);
        }

        if ($this->_isCheckout() && $this->_isProductOutOfStock($product)) {
            $item['is_configure_disabled'] = true;
            return $this->_updateItem(
                $item,
                Data::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK
            );
        }

        return [];
    }

    /**
     * Add qty data to product item.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $item
     * @return array|null
     */
    protected function addQtyData(\Magento\Catalog\Api\Data\ProductInterface $product, array $item)
    {
        if ($this->_isFrontend() && !$item['is_qty_disabled']) {
            $qtyStatus = $this->getQtyStatus($product, $item['qty']);
            if ($qtyStatus === true) {
                return $this->_updateItem($item, Data::ADD_ITEM_STATUS_SUCCESS);
            } else {
                $item['code'] = $qtyStatus['status'];
                unset($qtyStatus['status']);
                // Add qty_max_allowed and qty_min_allowed, if present
                $item = array_merge($item, $qtyStatus);
                return $item;
            }
        }

        return null;
    }

    /**
     * Validate configured product.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $item
     * @param array $config
     * @return array
     */
    protected function validateConfiguredProduct(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        array $item,
        $config
    ) {
        if (!$this->productConfiguration->isProductConfigured($product, $config)) {
            $failCode = !$this->_isFrontend() || $product->isVisibleInSiteVisibility()
                ? Data::ADD_ITEM_STATUS_FAILED_CONFIGURE
                : Data::ADD_ITEM_STATUS_FAILED_SKU;

            return $this->_updateItem($item, $failCode);
        }

        return [];
    }

    /**
     * Returns affected items.
     * Return format:
     * [
     *  'sku' => string
     *  'result' => string (see \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_*)
     *  'is_error' => int
     *  'name' => string
     *  'url' => string
     *  'price' => string
     *  'thumbnail_url' => string
     *  'qty' => string*
     * ]
     *
     * @param null|int $storeId [optional]
     * @return array
     *
     * @see prepareAddProductsBySku()
     */
    public function getAffectedItems($storeId = null)
    {
        $storeId = $storeId === null ? $this->_storeManager->getStore()->getId() : (int)$storeId;
        $affectedItems = $this->affectedItems;

        return isset($affectedItems[$storeId]) && is_array($affectedItems[$storeId]) ? $affectedItems[$storeId] : [];
    }

    /**
     * Set affected items.
     *
     * @param array $items
     * @param null|int $storeId [optional]
     * @return $this
     */
    public function setAffectedItems($items, $storeId = null)
    {
        $storeId = $storeId === null ? $this->_storeManager->getStore()->getId() : (int)$storeId;
        $affectedItems = $this->affectedItems;
        if (!is_array($affectedItems)) {
            $affectedItems = [];
        }

        $affectedItems[$storeId] = $items;
        $this->affectedItems = $affectedItems;
        return $this;
    }

    /**
     * Add processed item to stack.
     * Return format:
     * [
     *  'sku' => string
     *  'result' => string (see \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_*)
     *  'isError' => int
     *  'name' => string
     *  'url' => string
     *  'price' => string
     *  'thumbnailUrl' => string
     *  'qty' => string*
     * ]
     *
     * @param array $item
     * @param string $code
     * @return array|$this
     */
    protected function _addAffectedItem($item, $code)
    {
        if (!isset($item['sku']) || $code == Data::ADD_ITEM_STATUS_FAILED_EMPTY) {
            return $this;
        }

        $sku = $item['sku'];
        $affectedItems = $this->getAffectedItems();
        $affectedItems[$sku] = [
            'sku' => $sku,
            'result' => $this->getResultMessage($code),
            'isError' => (int) $this->isError($code),
            'name' => $this->getItemParam($item, 'name', $code),
            'url' => $this->getItemParam($item, 'url', $code),
            'price' => $this->getItemParam($item, 'price', $code),
            'thumbnailUrl' => $this->getItemParam($item, 'thumbnail_url', $code),
            'qty' => $this->getItemParam($item, 'qty')
        ];

        $this->_currentlyAffectedItems[] = $sku;
        $this->setAffectedItems($affectedItems);
        return $affectedItems[$sku];
    }

    /**
     * Is code type error.
     *
     * @param string $code
     * @return bool
     */
    protected function isError($code)
    {
        $allowedCodes = [
            Data::ADD_ITEM_STATUS_SUCCESS,
            Data::ADD_ITEM_STATUS_FAILED_CONFIGURE
        ];

        return (bool) !in_array($code, $allowedCodes);
    }

    /**
     * Item param getter.
     *
     * @param array $item
     * @param string $code
     * @param string|null $itemCode
     * @return string
     */
    private function getItemParam(array $item, $code, $itemCode = null)
    {
        if ($itemCode !== null
            && ($itemCode == Data::ADD_ITEM_STATUS_FAILED_SKU
                || $itemCode == Data::ADD_ITEM_STATUS_FAILED_DISABLED)
        ) {
            return '';
        }

        return isset($item[$code])
            ? $item[$code]
            : '';
    }

    /**
     * Get result message.
     *
     * @param string $code
     * @return string
     */
    protected function getResultMessage($code)
    {
        $message = $this->_checkoutData->getMessage($code);
        if (($message === '') && ($code === Data::ADD_ITEM_STATUS_FAILED_QTY_INCREMENTS)) {
            $message = __('You should correct the quantity for the product.');
        }
        if ($code === Data::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK) {
            $message = __('The SKU is out of stock.');
        }
        if ($code === Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED) {
            $message = __('We don\'t have the quantity you requested.');
        }

        return (string) $message;
    }

    /**
     * Add extra data to product item.
     *
     * @param array $item
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    protected function addExtraData(array $item, \Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if (empty($item['qty'])) {
            $item['qty'] = null;
        }
        $item['name'] = $product->getName();
        $item['price'] = $this->retrieveProductPrice($product, $item['qty']);
        $item['thumbnail_url'] = $this->getProductThumbnailUrl($product);
        $item['url'] = $product->getProductUrl();
        $item['id'] = $product->getId();
        $item['is_qty_disabled'] = $this->productTypeConfig->isProductSet($product->getTypeId());

        return $item;
    }

    /**
     * Retrieve product price.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param float $qty
     * @return float
     */
    private function retrieveProductPrice(\Magento\Catalog\Api\Data\ProductInterface $product, $qty)
    {
        $store = $product->getStore();
        $price = $this->priceCurrency->convertAndFormat(
            $product->getFinalPrice($qty),
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );

        return $price;
    }

    /**
     * Get product thumbnail url.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string
     */
    protected function getProductThumbnailUrl(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return $this->imageHelper->init($product, 'product_thumbnail_image')
            ->getUrl();
    }

    /**
     * Gets minimal sales quantity.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return int|null
     */
    protected function getProductDefaultQty(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }

    /**
     * Gets minimal sales quantity.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     */
    protected function getMinimalQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minSaleQty = $stockItem->getMinSaleQty();
        return $minSaleQty > 0 ? $minSaleQty : null;
    }

    /**
     * Add single item to stack and return extended pushed item. For return format see _addAffectedItem().
     *
     * @param string $sku
     * @param float $qty
     * @param array $config Configuration data of the product (if has been configured) [optional]
     * @return array
     */
    public function prepareAddProductBySku($sku, $qty, $config = [])
    {
        $affectedItems = $this->getAffectedItems();

        if (isset($affectedItems[$sku])) {
            /*
             * This condition made for case when user inputs same SKU in several rows. We need to update qty, otherwise
             * getQtyStatus() may return invalid result. If there's already such SKU in affected items array it means
             * that both came from add form (not from error grid as the case when there is several products with same
             * SKU requiring attention is not possible), so there could be no config.
             */
            if (empty($qty)) {
                /** @var $product \Magento\Catalog\Api\Data\ProductInterface */
                $product = $this->_loadProductWithOptionsBySku($sku, $config);
                if ($product) {
                    $qty = $this->getProductDefaultQty($product);
                }
            }
            if (!empty($affectedItems[$sku]['qty'])) {
                $qty += $affectedItems[$sku]['qty'];
            }
            unset($affectedItems[$sku]);
            $this->setAffectedItems($affectedItems);
        }

        $checkedItem = $this->checkItem($sku, $qty, $config);
        $code = $checkedItem['code'];
        unset($checkedItem['code']);
        return $this->_addAffectedItem($checkedItem, $code);
    }
}
