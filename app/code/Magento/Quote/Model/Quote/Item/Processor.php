<?php
namespace Magento\Quote\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Class Processor
 *  - initializes quote item with store_id and qty data
 *  - updates quote item qty and custom price data
 */
class Processor
{
    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param ItemFactory $quoteItemFactory
     * @param StoreManagerInterface $storeManager
     * @param State $appState
     */
    public function __construct(
        ItemFactory $quoteItemFactory,
        StoreManagerInterface $storeManager,
        State $appState
    ) {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
    }

    /**
     * Initialize quote item object
     *
     * @param DataObject $request
     * @param Product $product
     *
     * @return Item
     */
    public function init(Product $product, DataObject $request): Item
    {
        $item = $this->quoteItemFactory->create();

        $this->setItemStoreId($item);

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        if ($request->getResetCount() && !$product->getStickWithinParent() && $item->getId() === $request->getId()) {
            $item->setData(CartItemInterface::KEY_QTY, 0);
        }

        return $item;
    }

    /**
     * Set qty and custom price for quote item
     *
     * @param Item $item
     * @param DataObject $request
     * @param Product $candidate
     * @return void
     */
    public function prepare(Item $item, DataObject $request, Product $candidate): void
    {
        /**
         * We specify qty after we know about parent (for stock)
         */
        if ($request->getResetCount() && !$candidate->getStickWithinParent() && $item->getId() == $request->getId()) {
            $item->setData(CartItemInterface::KEY_QTY, 0);
        }
        $item->addQty($candidate->getCartQty());

        $customPrice = $request->getCustomPrice();
        if (!empty($customPrice)) {
            $item->setCustomPrice($customPrice);
            $item->setOriginalCustomPrice($customPrice);
        }
    }

    /**
     * Merge two quote items.
     *
     * @param Item $source
     * @param Item $target
     * @return Item
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function merge(Item $source, Item $target): Item
    {
        return $target;
    }

    /**
     * Set store_id value to quote item
     *
     * @param Item $item
     * @return void
     */
    protected function setItemStoreId(Item $item): void
    {
        if ($this->appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            $storeId = $this->storeManager->getStore($this->storeManager->getStore()->getId())
                ->getId();
            $item->setStoreId($storeId);
        } else {
            $item->setStoreId($this->storeManager->getStore()->getId());
        }
    }
}
