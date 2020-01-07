<?php
namespace Magento\RequisitionList\Block\Requisition\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\RequisitionList\Model\Checker\ProductChangesAvailability;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\RequisitionListItem\Validator\Sku as SkuValidator;
use Magento\RequisitionList\Model\RequisitionListItemProduct;
use Magento\Framework\App\ObjectManager;
use Magento\RequisitionList\Model\RequisitionListItemOptionsLocator;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;

/**
 * View block for requisition list item.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.0
 */
class Item extends \Magento\Framework\View\Element\Template
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @var ProductChangesAvailability
     */
    private $productChangesAvailabilityChecker;

    /**
     * @var RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * @var RequisitionListItemOptionsLocator
     */
    private $itemOptionsLocator;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var RequisitionListItemInterface
     */
    private $item;

    /**
     * @var array
     */
    private $itemErrors = [];

    /**
     * @param Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductChangesAvailability $productChangesAvailabilityChecker
     * @param RequisitionListItemProduct $requisitionListItemProduct
     * @param array $data [optional]
     * @param RequisitionListItemOptionsLocator $itemOptionsLocator
     * @param ItemResolverInterface $itemResolver
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        ProductChangesAvailability $productChangesAvailabilityChecker,
        RequisitionListItemProduct $requisitionListItemProduct,
        array $data = [],
        RequisitionListItemOptionsLocator $itemOptionsLocator = null,
        ItemResolverInterface $itemResolver = null
    ) {
        parent::__construct($context, $data);
        $this->imageHelper = $context->getImageHelper();
        $this->taxHelper = $context->getTaxData();
        $this->catalogHelper = $context->getCatalogHelper();
        $this->priceCurrency = $priceCurrency;
        $this->productChangesAvailabilityChecker = $productChangesAvailabilityChecker;
        $this->requisitionListItemProduct = $requisitionListItemProduct;

        $this->itemOptionsLocator = $itemOptionsLocator
            ?? ObjectManager::getInstance()->get(RequisitionListItemOptionsLocator::class);
        $this->itemResolver = $itemResolver
            ?? ObjectManager::getInstance()->get(ItemResolverInterface::class);
    }

    /**
     * Set requisition list item.
     *
     * @param RequisitionListItemInterface $item
     * @return $this
     */
    public function setItem(RequisitionListItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get requisition list item.
     *
     * @return RequisitionListItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set requisition list item errors.
     *
     * @param array $errors
     * @return $this
     */
    public function setItemErrors(array $errors)
    {
        $this->itemErrors = $errors;
        return $this;
    }

    /**
     * Get requisition list item errors.
     *
     * @return array
     */
    public function getItemErrors()
    {
        return $this->itemErrors;
    }

    /**
     * Get product from requisition list item.
     *
     * @return ProductInterface|null
     */
    public function getRequisitionListProduct()
    {
        $item = $this->getItem();

        if (!$this->requisitionListItemProduct->isProductAttached($item) && !$this->isOptionsUpdated()) {
            return null;
        }

        try {
            $product = $this->requisitionListItemProduct->getProduct($item);
            return $product;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Format product price including tax.
     *
     * @return float
     */
    public function getFormattedPrice()
    {
        return $this->formatProductPrice($this->getFinalPrice($this->displayPriceIncludingTax()));
    }

    /**
     * Format product price excluding tax.
     *
     * @return float
     */
    public function getFormattedPriceExcludingTax()
    {
        return $this->formatProductPrice($this->getFinalPrice(false));
    }

    /**
     * Format subtotal price of item including tax.
     *
     * @return float
     */
    public function getFormattedSubtotal()
    {
        return $this->formatProductPrice(
            $this->getFinalPrice($this->displayPriceIncludingTax()) * $this->getItem()->getQty()
        );
    }

    /**
     * Format subtotal price of item excluding tax.
     *
     * @return float
     */
    public function getFormattedSubtotalExcludingTax()
    {
        return $this->formatProductPrice($this->getFinalPrice(false) * $this->getItem()->getQty());
    }

    /**
     * Get formatted price.
     *
     * @param float $value
     * @return float
     */
    private function formatProductPrice($value)
    {
        return $this->priceCurrency->convertAndFormat(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->_storeManager->getStore()
        );
    }

    /**
     * Get url of product image from requisition list item.
     *
     * @return string|null
     */
    public function getImageUrl()
    {
        try {
            $product = $this->itemResolver->getFinalProduct($this->itemOptionsLocator->getOptions($this->getItem()));
            $imageUrl = $this->imageHelper->getDefaultPlaceholderUrl('thumbnail');
            if ($product !== null) {
                $imageUrl = $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
            }
            return $imageUrl;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get url of product from requisition list item.
     *
     * @return string|null
     */
    public function getProductUrlByItem()
    {
        try {
            $product = $this->requisitionListItemProduct->getProduct($this->getItem());
            return $product->getProductUrl();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get url to configure product from requisition list item.
     *
     * @return string
     */
    public function getItemConfigureUrl()
    {
        return $this->getUrl(
            'requisition_list/item/configure',
            [
                'item_id' => $this->getItem()->getItemId(),
                'id' => $this->requisitionListItemProduct->getProduct($this->getItem())->getId(),
                'requisition_id' => $this->getItem()->getRequisitionListId()
            ]
        );
    }

    /**
     * Check if we should display in catalog prices including and excluding tax.
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        return $this->taxHelper->displayBothPrices($this->_storeManager->getStore());
    }

    /**
     * Check if we should display in catalog prices including tax.
     *
     * @return bool
     */
    private function displayPriceIncludingTax()
    {
        try {
            $product = $this->requisitionListItemProduct->getProduct($this->getItem());
            $adjustment = $product->getPriceInfo()->getAdjustment(\Magento\Tax\Pricing\Adjustment::ADJUSTMENT_CODE);
            return $adjustment->isIncludedInDisplayPrice();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Get final price of product.
     *
     * @param bool $includingTax [optional]
     * @return float|null
     */
    private function getFinalPrice($includingTax = false)
    {
        try {
            $product = $this->requisitionListItemProduct->getProduct($this->getItem());

            return $this->catalogHelper->getTaxPrice(
                $product,
                $product->getPriceModel()->getFinalPrice($this->getItem()->getQty(), $product),
                $includingTax,
                null,
                null,
                null,
                $this->_storeManager->getStore(),
                null,
                false
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Check if edit action is available for requisition list item.
     *
     * @return bool
     */
    public function canEdit()
    {
        if ($this->isOptionsUpdated()) {
            return true;
        }
        if (!$this->getRequisitionListProduct()) {
            return false;
        }

        try {
            $product = $this->requisitionListItemProduct->getProduct($this->getItem());
            return $this->productChangesAvailabilityChecker->isProductEditable($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Check if product quantity can be changed for requisition list item.
     *
     * @return bool
     */
    public function canEditQty()
    {
        $item = $this->getItem();

        try {
            $product = $this->requisitionListItemProduct->getProduct($item);
            return $this->requisitionListItemProduct->isProductAttached($item)
                || $this->productChangesAvailabilityChecker->isQtyChangeAvailable($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Check if product options were updated and product should be reconfigured.
     *
     * @return bool
     */
    public function isOptionsUpdated()
    {
        return !empty($this->itemErrors[SkuValidator::ERROR_OPTIONS_UPDATED]);
    }

    /**
     * Get validation error for requisition list item.
     *
     * @return string|null
     */
    public function getItemError()
    {
        return !empty($this->itemErrors) ? reset($this->itemErrors) : null;
    }
}
