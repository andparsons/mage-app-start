<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Items;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\NegotiableQuote\Model\Quote\TotalsFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml sales order create items grid block.
 *
 * @api
 * @since 100.0.0
 */
class Grid extends \Magento\Backend\Block\Widget
{
    /**
     * @var SalesGrid
     */
    private $salesGridBlock;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $productConfigurationPool;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param SalesGrid $salesGridBlock
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $productConfigurationPool
     * @param TotalsFactory $quoteTotalsFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        SalesGrid $salesGridBlock,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Catalog\Helper\Product\ConfigurationPool $productConfigurationPool,
        TotalsFactory $quoteTotalsFactory,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->salesGridBlock = $salesGridBlock;
        $this->restriction = $restriction;
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
        $this->taxConfig = $taxConfig;
        $this->productConfigurationPool = $productConfigurationPool;
        $this->quoteTotalsFactory = $quoteTotalsFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotes_quote_index');
    }

    /**
     * Retrieve current quote.
     *
     * @param bool $snapshot
     * @return CartInterface|null
     */
    public function getQuote($snapshot = false)
    {
        return $this->negotiableQuoteHelper->resolveCurrentQuote($snapshot);
    }

    /**
     * Retrieve product url.
     *
     * @param Item $item
     * @return string
     */
    public function getProductUrlByItem(Item $item)
    {
        $params = [
            'id' => $item->getProduct()->getId()
        ];
        return $this->getUrl('catalog/product/edit', $params);
    }

    /**
     * Get items.
     *
     * @return Item[]
     */
    public function getItems()
    {
        $quote = $this->getQuote(true);
        if ($this->restriction->canSubmit()) {
            $quote->collectTotals();
        }
        $this->salesGridBlock->setQuote($quote);
        $this->salesGridBlock->setNameInLayout($this->getNameInLayout());
        return $this->salesGridBlock->getItems();
    }

    /**
     * Format catalog price.
     *
     * @param Item $item
     * @return float
     */
    public function getFormattedCatalogPrice(Item $item)
    {
        return $this->negotiableQuoteHelper
            ->getFormattedCatalogPrice($item, $this->getQuote()->getCurrency()->getBaseCurrencyCode());
    }

    /**
     * Format original price.
     *
     * @param Item $item
     * @return float
     */
    public function getFormattedOriginalPrice(Item $item)
    {
        return $this->negotiableQuoteHelper
            ->getFormattedOriginalPrice($item, $this->getQuote()->getCurrency()->getBaseCurrencyCode());
    }

    /**
     * Format cart price.
     *
     * @param Item $item
     * @return float
     */
    public function getFormattedCartPrice(Item $item)
    {
        return $this->negotiableQuoteHelper
            ->getFormattedCartPrice($item, $this->getQuote()->getCurrency()->getBaseCurrencyCode());
    }

    /**
     * Display subtotal.
     *
     * @param Item $item
     * @return string
     */
    public function getFormattedSubtotal(Item $item)
    {
        return $this->formatBaseCurrency($item->getBaseRowTotal() - $item->getBaseDiscountAmount());
    }

    /**
     * Display cost.
     *
     * @param Item $item
     * @return string
     */
    public function getFormattedCost(Item $item)
    {
        $totals = $this->quoteTotalsFactory->create(['quote' => $this->getQuote()]);
        $cost = $totals->getItemCost($item);
        return $this->formatBaseCurrency($cost);
    }

    /**
     * @return bool
     */
    public function canEdit()
    {
        return $this->restriction->canSubmit();
    }

    /**
     * @return bool
     */
    public function canUpdate()
    {
        return $this->restriction->canCurrencyUpdate();
    }

    /**
     * Get subtotal incl. or excl. label.
     *
     * @return string
     */
    public function getSubtotalTaxLabel()
    {
        return $this->isTaxDisplayedWithSubtotal()
            ? __('Subtotal (Incl. Tax)') : __('Subtotal (Excl. Tax)') ;
    }

    /**
     * Get formatted tax amount for quote item.
     *
     * @param Item $item
     * @return string
     */
    public function getItemTaxAmount(Item $item)
    {
        return $this->formatBaseCurrency($item->getBaseTaxAmount());
    }

    /**
     * Get item subtotal include or exclude tax amount.
     *
     * @param Item $item
     * @return string
     */
    public function getItemSubtotalTaxValue(Item $item)
    {
        $subtotal = $this->isTaxDisplayedWithSubtotal()
            ? $item->getBaseRowTotal() + $item->getBaseTaxAmount()
            : $item->getBaseRowTotal();
        return $this->formatBaseCurrency($subtotal - $item->getBaseDiscountAmount());
    }

    /**
     * Get params for custom options.
     *
     * @return array
     */
    public function getParamsForCustomOptions()
    {
        return [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
    }

    /**
     * Is tax included to subtotal value.
     *
     * @return bool
     */
    protected function isTaxDisplayedWithSubtotal()
    {
        return $this->taxConfig->displaySalesSubtotalInclTax($this->_storeManager->getStore())
            || $this->taxConfig->displaySalesSubtotalBoth($this->_storeManager->getStore());
    }

    /**
     * Retrieves item options.
     *
     * @param Item $item
     * @return array
     */
    public function getProductOptions(Item $item)
    {
        $configuration = $this->productConfigurationPool->getByProductType($item->getProductType());
        return $configuration->getOptions($item);
    }

    /**
     * Format price in quote base currency.
     *
     * @param  float $price
     * @return string
     */
    private function formatBaseCurrency($price)
    {
        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $this->getQuote()->getCurrency()->getBaseCurrencyCode()
        );
    }
}
