<?php

namespace Magento\NegotiableQuote\Model\Plugin\Checkout\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CartPlugin
 */
class CartPlugin
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * CartPlugin constructor.
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        ManagerInterface $messageManager
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * Around addOrderItem plugin
     *
     * @param Cart $subject
     * @param \Closure $proceed
     * @param Item $orderItem
     * @param bool|null $qtyFlag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddOrderItem(
        Cart $subject,
        \Closure $proceed,
        Item $orderItem,
        $qtyFlag = null
    ) {
        /* @var $orderItem Item */
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return $this->addSkuNotFoundError($orderItem);
            }

            if ($product->isDisabled()) {
                return $this->addSkuNotFoundError($orderItem);
            }

            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            if (!$info) {
                $info = [];
            }
            $info = new \Magento\Framework\DataObject($info);

            if ($qtyFlag === null) {
                $info->setQty($orderItem->getQtyOrdered());
            }

            if ($qtyFlag !== null) {
                $info->setQty(1);
            }

            $subject->addProduct($product, $info);
        }
        return $this;
    }

    /**
     * Add sku not found error message
     *
     * @param Item $orderItem
     * @return $this
     */
    protected function addSkuNotFoundError(Item $orderItem)
    {
        $this->messageManager->addError(__('Product with SKU %1 not found', $orderItem->getSku()));
        return $this;
    }
}
