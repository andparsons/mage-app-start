<?php

namespace Magento\Weee\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\Store;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class Weee extends AbstractTotal
{
    /**
     * Constant for weee item code prefix
     */
    const ITEM_CODE_WEEE_PREFIX = 'weee';
    /**
     * Constant for weee item type
     */
    const ITEM_TYPE = 'weee';

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeData;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * Counter
     *
     * @var int
     */
    protected $counter = 0;

    /**
     * Array to keep track of weee taxable item code to quote item
     *
     * @var array
     */
    protected $weeeCodeToItemMap;

    /**
     * Accumulates totals for Weee excluding tax
     *
     * @var int
     */
    protected $weeeTotalExclTax;

    /**
     * Accumulates totals for Weee base excluding tax
     *
     * @var int
     */
    protected $weeeBaseTotalExclTax;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Weee\Helper\Data $weeeData,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->weeeData = $weeeData;
        $this->setCode('weee');
        $this->weeeCodeToItemMap = [];
    }

    /**
     * Collect Weee amounts for the quote / order
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        AbstractTotal::collect($quote, $shippingAssignment, $total);
        $this->_store = $quote->getStore();
        if (!$this->weeeData->isEnabled($this->_store)) {
            return $this;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $this->weeeTotalExclTax = 0;
        $this->weeeBaseTotalExclTax = 0;
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $this->resetItemData($item);
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->resetItemData($child);
                    $this->process($address, $total, $child);
                }
                $this->recalculateParent($item);
            } else {
                $this->process($address, $total, $item);
            }
        }
        $total->setWeeeCodeToItemMap($this->weeeCodeToItemMap);
        $total->setWeeeTotalExclTax($this->weeeTotalExclTax);
        $total->setWeeeBaseTotalExclTax($this->weeeBaseTotalExclTax);
        return $this;
    }

    /**
     * Calculate item fixed tax and prepare information for discount and regular taxation
     *
     * @param   \Magento\Quote\Model\Quote\Address $address
     * @param   \Magento\Quote\Model\Quote\Address\Total $total
     * @param   \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return  void|$this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function process(
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Quote\Model\Quote\Address\Total $total,
        $item
    ) {
        $attributes = $this->weeeData->getProductWeeeAttributes(
            $item->getProduct(),
            $address,
            $address->getQuote()->getBillingAddress(),
            $this->_store->getWebsiteId()
        );

        $productTaxes = [];

        $totalValueInclTax = 0;
        $baseTotalValueInclTax = 0;
        $totalRowValueInclTax = 0;
        $baseTotalRowValueInclTax = 0;

        $totalValueExclTax = 0;
        $baseTotalValueExclTax = 0;
        $totalRowValueExclTax = 0;
        $baseTotalRowValueExclTax = 0;

        $associatedTaxables = $item->getAssociatedTaxables();
        if (!$associatedTaxables) {
            $associatedTaxables = [];
        } else {
            // remove existing weee associated taxables
            foreach ($associatedTaxables as $iTaxable => $taxable) {
                if ($taxable[CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE] == self::ITEM_TYPE) {
                    unset($associatedTaxables[$iTaxable]);
                }
            }
        }

        foreach ($attributes as $key => $attribute) {
            $title = $attribute->getName();

            $baseValueExclTax = $baseValueInclTax = $attribute->getAmount();
            $valueExclTax = $valueInclTax = $this->priceCurrency->round(
                $this->priceCurrency->convert($baseValueExclTax, $this->_store)
            );

            $rowValueInclTax = $rowValueExclTax = $this->priceCurrency->round($valueInclTax * $item->getTotalQty());
            $baseRowValueInclTax = $this->priceCurrency->round($baseValueInclTax * $item->getTotalQty());
            $baseRowValueExclTax = $baseRowValueInclTax;

            $totalValueInclTax += $valueInclTax;
            $baseTotalValueInclTax += $baseValueInclTax;
            $totalRowValueInclTax += $rowValueInclTax;
            $baseTotalRowValueInclTax += $baseRowValueInclTax;

            $totalValueExclTax += $valueExclTax;
            $baseTotalValueExclTax += $baseValueExclTax;
            $totalRowValueExclTax += $rowValueExclTax;
            $baseTotalRowValueExclTax += $baseRowValueExclTax;

            $productTaxes[] = [
                'title' => $title,
                'base_amount' => $baseValueExclTax,
                'amount' => $valueExclTax,
                'row_amount' => $rowValueExclTax,
                'base_row_amount' => $baseRowValueExclTax,
                'base_amount_incl_tax' => $baseValueInclTax,
                'amount_incl_tax' => $valueInclTax,
                'row_amount_incl_tax' => $rowValueInclTax,
                'base_row_amount_incl_tax' => $baseRowValueInclTax,
            ];

            if ($this->weeeData->isTaxable($this->_store)) {
                $weeeItemCode = self::ITEM_CODE_WEEE_PREFIX . $this->getNextIncrement();
                $weeeItemCode .= '-' . $title;

                $associatedTaxables[] = [
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => self::ITEM_TYPE,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => $weeeItemCode,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $valueExclTax,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $baseValueExclTax,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => $item->getTotalQty(),
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $item->getProduct()->getTaxClassId(),
                ];
                $this->weeeCodeToItemMap[$weeeItemCode] = $item;
            }
        }
        $item->setAssociatedTaxables($associatedTaxables);

        $item->setWeeeTaxAppliedAmount($totalValueExclTax)
            ->setBaseWeeeTaxAppliedAmount($baseTotalValueExclTax)
            ->setWeeeTaxAppliedRowAmount($totalRowValueExclTax)
            ->setBaseWeeeTaxAppliedRowAmnt($baseTotalRowValueExclTax);

        $item->setWeeeTaxAppliedAmountInclTax($totalValueInclTax)
            ->setBaseWeeeTaxAppliedAmountInclTax($baseTotalValueInclTax)
            ->setWeeeTaxAppliedRowAmountInclTax($totalRowValueInclTax)
            ->setBaseWeeeTaxAppliedRowAmntInclTax($baseTotalRowValueInclTax);

        $this->processTotalAmount(
            $total,
            $totalRowValueExclTax,
            $baseTotalRowValueExclTax,
            $totalRowValueInclTax,
            $baseTotalRowValueInclTax
        );

        $this->weeeData->setApplied($item, array_merge($this->weeeData->getApplied($item), $productTaxes));
    }

    /**
     * Process row amount based on FPT total amount configuration setting
     *
     * @param   \Magento\Quote\Model\Quote\Address\Total $total
     * @param   float $rowValueExclTax
     * @param   float $baseRowValueExclTax
     * @param   float $rowValueInclTax
     * @param   float $baseRowValueInclTax
     * @return  $this
     */
    protected function processTotalAmount(
        $total,
        $rowValueExclTax,
        $baseRowValueExclTax,
        $rowValueInclTax,
        $baseRowValueInclTax
    ) {
        if (!$this->weeeData->isTaxable($this->_store)) {
            //Accumulate the values.  Will be used later in the 'weee tax' collector
            $this->weeeTotalExclTax += $this->priceCurrency->round($rowValueExclTax);
            $this->weeeBaseTotalExclTax += $this->priceCurrency->round($baseRowValueExclTax);
        }

        //This value is used to calculate shipping cost; it will be overridden by tax collector
        $total->setSubtotalInclTax(
            $total->getSubtotalInclTax() + $this->priceCurrency->round($rowValueInclTax)
        );
        $total->setBaseSubtotalInclTax(
            $total->getBaseSubtotalInclTax() + $this->priceCurrency->round($baseRowValueInclTax)
        );
        return $this;
    }

    /**
     * Increment and return counter. This function is intended to be used to generate temporary
     * id for an item.
     *
     * @return int
     */
    protected function getNextIncrement()
    {
        return ++$this->counter;
    }

    /**
     * Recalculate parent item amounts based on children results
     *
     * @param   \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return  void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function recalculateParent(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $associatedTaxables = [];
        foreach ($item->getChildren() as $child) {
            $associatedTaxables = array_merge($associatedTaxables, $child->getAssociatedTaxables());
        }
        $item->setAssociatedTaxables($associatedTaxables);
    }

    /**
     * Reset information about Tax and Wee on FPT for shopping cart item
     *
     * @param   \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return  void
     */
    protected function resetItemData($item)
    {
        $this->weeeData->setApplied($item, []);

        $item->setAssociatedTaxables([]);

        $item->setBaseWeeeTaxDisposition(0);
        $item->setWeeeTaxDisposition(0);

        $item->setBaseWeeeTaxRowDisposition(0);
        $item->setWeeeTaxRowDisposition(0);

        $item->setBaseWeeeTaxAppliedAmount(0);
        $item->setBaseWeeeTaxAppliedRowAmnt(0);

        $item->setWeeeTaxAppliedAmount(0);
        $item->setWeeeTaxAppliedRowAmount(0);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return null;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   Store $store
     * @return  array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }

    /**
     * No aggregated label for fixed product tax
     *
     * TODO: fix
     * @return string
     */
    public function getLabel()
    {
        return '';
    }
}
