<?php

namespace Magento\RequisitionList\Model\RequisitionListItem\Validator;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\RequisitionListItem\ValidatorInterface;
use Magento\RequisitionList\Model\OptionsManagement;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\RequisitionList\Model\RequisitionListItemProduct;

/**
 * Class validates that product from requisition list is available and product options are configured.
 */
class Sku implements ValidatorInterface
{
    /**
     * SKU is not available in catalog.
     */
    const ERROR_UNAVAILABLE_SKU = 'unavailable_sku';

    /**
     * Product options were updated and product should be reconfigured.
     */
    const ERROR_OPTIONS_UPDATED = 'options_updated';

    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement
     */
    private $optionsManagement;

    /**
     * @var RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * @param OptionsManagement $optionsManagement
     * @param RequisitionListItemProduct $requisitionListItemProduct
     */
    public function __construct(
        OptionsManagement $optionsManagement,
        RequisitionListItemProduct $requisitionListItemProduct
    ) {
        $this->optionsManagement = $optionsManagement;
        $this->requisitionListItemProduct = $requisitionListItemProduct;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate(RequisitionListItemInterface $item)
    {
        $errors = [];
        $product = $this->requisitionListItemProduct->getProduct($item);

        if ($this->requisitionListItemProduct->isProductAttached($item) === false || !$product
            || $product->getStatus() == ProductStatus::STATUS_DISABLED) {
            $errors[self::ERROR_UNAVAILABLE_SKU] = __('The SKU was not found in the catalog.');
        } elseif (!$this->isValidDependentProducts($item)) {
            $errors[self::ERROR_OPTIONS_UPDATED] = __('Options were updated. Please review available configurations.');
        }

        if (count($errors)) {
            $this->requisitionListItemProduct->setIsProductAttached($item, false);
        }

        return $errors;
    }

    /**
     * Check if product options were updated.
     *
     * @param RequisitionListItemInterface $item
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isValidDependentProducts(RequisitionListItemInterface $item)
    {
        $product = $this->requisitionListItemProduct->getProduct($item);

        if ($product->isComposite()) {
            /** @var \Magento\Catalog\Model\Product\Type\AbstractType $productTypeInstance */
            $productTypeInstance = $product->getTypeInstance();
            $buyRequestData = $this->optionsManagement->getInfoBuyRequest($item);

            if (!$buyRequestData) {
                return false;
            }

            $buyRequest = new \Magento\Framework\DataObject($buyRequestData);
            $cartCandidates = $productTypeInstance->prepareForCartAdvanced($buyRequest, $product);
            if (!is_array($cartCandidates)) {
                return false;
            }
        }
        $itemOptions = $item->getOptions();
        if ($product->hasOptions() && isset($itemOptions['option_ids'])) {
            $selectionOptionIds = explode(",", $itemOptions['option_ids']);
            $optionIds = [];
            foreach ($product->getOptions() as $option) {
                $optionIds[] = $option->getOptionId();
            }
            if (array_diff($selectionOptionIds, $optionIds)) {
                return false;
            }
        }

        return true;
    }
}
