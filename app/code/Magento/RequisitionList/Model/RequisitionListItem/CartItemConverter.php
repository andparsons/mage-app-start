<?php

namespace Magento\RequisitionList\Model\RequisitionListItem;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Api\Data\ProductOptionInterfaceFactory;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\RequisitionList\Model\RequisitionListItemProduct;

/**
 * Requisition List Item to Cart Item converter.
 */
class CartItemConverter
{
    /**
     * @var string
     */
    private $buyRequestOptionCode = 'info_buyRequest';

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    private $cartItemFactory;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionInterfaceFactory
     */
    private $productOptionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor
     */
    private $cartItemProcessor;

    /**
     * @var RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $productSkus = [];

    /**
     * @param CartItemInterfaceFactory $cartItemFactory
     * @param ProductOptionInterfaceFactory $productOptionFactory
     * @param CartItemOptionsProcessor $cartItemProcessor
     * @param RequisitionListItemProduct $requisitionListItemProduct
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CartItemInterfaceFactory $cartItemFactory,
        ProductOptionInterfaceFactory $productOptionFactory,
        CartItemOptionsProcessor $cartItemProcessor,
        RequisitionListItemProduct $requisitionListItemProduct,
        SerializerInterface $serializer
    ) {
        $this->cartItemFactory = $cartItemFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->cartItemProcessor = $cartItemProcessor;
        $this->requisitionListItemProduct = $requisitionListItemProduct;
        $this->serializer = $serializer;
    }

    /**
     * Convert requisition item to a cart item.
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @return \Magento\Quote\Api\Data\CartItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convert(RequisitionListItemInterface $requisitionListItem)
    {
        /** @var \Magento\Quote\Api\Data\CartItemInterface | \Magento\Quote\Model\Quote\Item $cartItem */
        $cartItem = $this->cartItemFactory->create();
        $cartItem->setSku($requisitionListItem->getSku());
        $cartItem->setQty($requisitionListItem->getQty());
        $product = $this->retrieveProduct($requisitionListItem);
        $productOption = $this->productOptionFactory->create();
        $productOption->setData($product->getCustomOptions());
        $cartItem->setProductOption($productOption);

        foreach ($product->getCustomOptions() as $customOption) {
            $cartItem->addOption($customOption);
        }

        $cartItem->setData('product', $product);

        $buyRequestOption = $cartItem->getOptionByCode($this->buyRequestOptionCode);
        $buyRequest = !empty($buyRequestOption)
            ? $buyRequestOption->getValue()
            : [];
        $buyRequestOption->setValue($this->serializer->serialize($buyRequest));

        $this->cartItemProcessor->addProductOptions($product->getTypeId(), $cartItem);
        $this->cartItemProcessor->applyCustomOptions($cartItem);

        return $cartItem;
    }

    /**
     * If product added in cart load product without cache.
     *
     * @param RequisitionListItemInterface $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function retrieveProduct(RequisitionListItemInterface $item)
    {
        if (in_array($item->getSku(), $this->productSkus)) {
            $product = $this->requisitionListItemProduct->getProduct($item, true);
        } else {
            $product = $this->requisitionListItemProduct->getProduct($item);
            $this->productSkus[] = $item->getSku();
        }
        return $product;
    }
}
