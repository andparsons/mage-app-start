<?php

namespace Magento\RequisitionList\Model\Checker;

/**
 * Class is responsible for checking availability of edit actions based on product type.
 */
class ProductChangesAvailability
{
    /**
     * @var array
     */
    private $productQtyChangeAvailabilityCheckers = [];

    /**
     * @var array
     */
    private $ignoreTypes = [];

    /**
     * @param array $productQtyChangeAvailabilityCheckers
     * @param array $ignoreTypes
     */
    public function __construct(
        array $productQtyChangeAvailabilityCheckers,
        array $ignoreTypes
    ) {
        $this->productQtyChangeAvailabilityCheckers = $productQtyChangeAvailabilityCheckers;
        $this->ignoreTypes = $ignoreTypes;
    }

    /**
     * Check if edit action is available for requisition list item basing on product type.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    public function isProductEditable(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $type = $product->getTypeId();
        return ($type === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            && $product->getTypeInstance()->hasOptions($product)) || !in_array($type, $this->ignoreTypes);
    }

    /**
     * Check if product qty for requisition list item can be changed.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    public function isQtyChangeAvailable(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $qtyChangeAvailable = true;

        /**
         * @var \Magento\RequisitionList\Model\Checker\ProductQtyChangeAvailabilityInterface $checker
         */
        foreach ($this->productQtyChangeAvailabilityCheckers as $checker) {
            if (!$checker->isAvailable($product)) {
                $qtyChangeAvailable = false;
                break;
            }
        }

        return $qtyChangeAvailable;
    }
}
