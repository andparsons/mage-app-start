<?php

namespace Magento\RequisitionList\Model\RequisitionListItem\Validator;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\RequisitionListItem\ValidatorInterface;
use Magento\RequisitionList\Model\RequisitionListItemProduct;

/**
 * Class is responsible for validation of product stock.
 */
class Stock implements ValidatorInterface
{
    /**
     * Product is out of stock.
     */
    const ERROR_OUT_OF_STOCK = 'out_of_stock';

    /**
     * Requested product quantity is greater than available quantity.
     */
    const ERROR_LOW_QUANTITY = 'low_quantity';

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param RequisitionListItemProduct $requisitionListItemProduct
     * @param StockStateInterface $stockState
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        RequisitionListItemProduct $requisitionListItemProduct,
        StockStateInterface $stockState
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->requisitionListItemProduct = $requisitionListItemProduct;
        $this->stockState = $stockState;
    }

    /**
     * Validate product stock.
     *
     * @param RequisitionListItemInterface $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate(RequisitionListItemInterface $item)
    {
        $errors = [];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->requisitionListItemProduct->getProduct($item);

        $stock = $this->stockRegistry->getStockStatus($product->getId());
        if ($stock->getStockStatus() === StockStatusInterface::STATUS_OUT_OF_STOCK) {
            $errors[self::ERROR_OUT_OF_STOCK] = __('The SKU is out of stock.');
            return $errors;
        }

        if (!$this->stockState->checkQty($product->getId(), $item->getQty()) && !$product->isComposite()) {
            $errors[self::ERROR_LOW_QUANTITY] =
                __('The requested qty is not available');
            return $errors;
        }

        return $errors;
    }
}
