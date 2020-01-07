<?php
namespace Magento\NegotiableQuote\Model\ResourceModel\Sku\Errors\Grid;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * SKU failed grid collection
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var \Magento\AdvancedCheckout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var null|integer
     */
    private $customerGroupId = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\AdvancedCheckout\Model\Cart $cart
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\AdvancedCheckout\Model\Cart $cart,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        PriceCurrencyInterface $priceCurrency,
        StockRegistryInterface $stockRegistry
    ) {
        $this->_cart = $cart;
        $this->productFactory = $productFactory;
        $this->priceCurrency = $priceCurrency;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($entityFactory);
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $parentBlock = $this->_cart;
            foreach ($parentBlock->getFailedItems() as $affectedItem) {
                // Escape user-submitted input
                if (isset($affectedItem['item']['qty'])) {
                    $affectedItem['item']['qty'] = empty($affectedItem['item']['qty'])
                        ? 0
                        : (float)$affectedItem['item']['qty'];
                }
                $item = new \Magento\Framework\DataObject();
                $item->setCode($affectedItem['code']);
                if (isset($affectedItem['error'])) {
                    $item->setError($affectedItem['error']);
                }
                $item->addData($affectedItem['item']);
                $item->setId($item->getSku());
                $product = $this->productFactory->create();
                if (isset($affectedItem['item']['id'])) {
                    $productId = $affectedItem['item']['id'];
                    $item->setProductId($productId);
                    $product->load($productId);
                    $stockStatus = $this->stockRegistry->getStockStatus($productId, $this->getWebsiteId());
                    if ($stockStatus !== null) {
                        $product->setIsSalable($stockStatus->getStockStatus());
                    }
                    $price = $this->getProductPrice($product, $affectedItem['item']['qty']);
                    $item->setPrice($this->priceCurrency->format($price, false));
                }
                $item->setProduct($product);
                $this->addItem($item);
            }
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * Retrieve product price
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param float $qty
     * @return array|float
     */
    private function getProductPrice(\Magento\Catalog\Api\Data\ProductInterface $product, $qty)
    {
        $product->setCustomerGroupId($this->customerGroupId);
        if ($product->getData('tier_price') == 1) {
            $product->setData('tier_price', null);
        }

        return $product->getTierPrice($qty);
    }

    /**
     * Get current website ID
     *
     * @return int|null
     */
    private function getWebsiteId()
    {
        return $this->_cart->getStore()->getWebsiteId();
    }

    /**
     * Setter for customerGroupId
     *
     * @param int $groupId
     * @return void
     */
    public function setCustomerGroupId($groupId)
    {
        $this->customerGroupId = $groupId;
    }
}
