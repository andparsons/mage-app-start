<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Order;

use Magento\NegotiableQuote\Model\Quote\Totals as QuoteTotals;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Totals.
 *
 * @api
 * @since 100.0.0
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface
     */
    private $negotiableQuote;

    /**
     * @var QuoteTotals
     */
    private $quoteTotals;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface;
     */
    private $priceCurrency;

    /**
     * @var array
     */
    protected $catalogTotals = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\NegotiableQuote\Model\Quote\TotalsFactory $quoteTotalsFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\NegotiableQuote\Model\Quote\TotalsFactory $quoteTotalsFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteRepository = $quoteRepository;
        $this->taxConfig = $taxConfig;
        $this->quoteTotalsFactory = $quoteTotalsFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Initialize totals object.
     *
     * @return $this
     */
    public function initTotals()
    {
        if ($this->isNegotiableQuote()) {
            $total = new \Magento\Framework\DataObject(
                [
                    'block_name' => 'order.total.catalog.price'
                ]
            );
            $this->catalogTotals = [];
            if ($this->isTaxDisplayedSalesSubtotalBoth()) {
                $catalogPriceExclTax = $this->getQuoteTotals()->getCatalogTotalPriceWithoutTax() -
                    $this->getQuoteTotals()->getCartTotalDiscount();
                $this->catalogTotals['catalog_price_excl_tax'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'catalog_price_excl_tax',
                        'value' => $this->convertToOrderCurrency($catalogPriceExclTax),
                        'base_value' => $catalogPriceExclTax,
                        'label' => __('Catalog Total Price (Excl. Tax)')
                    ]
                );
                $catalogPriceInclTax = $this->getQuoteTotals()->getCatalogTotalPriceWithTax() -
                    $this->getQuoteTotals()->getCartTotalDiscount();
                $this->catalogTotals['catalog_price_incl_tax'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'catalog_price_incl_tax',
                        'value' => $this->convertToOrderCurrency($catalogPriceInclTax),
                        'base_value' => $catalogPriceInclTax,
                        'label' => __('Catalog Total Price (Incl. Tax)')
                    ]
                );
            }
            if ($this->isTaxDisplayedSalesSubtotalInclTax() || $this->isTaxDisplayedSalesSubtotalExclTax()) {
                $this->catalogTotals['catalog_price'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'catalog_price',
                        'value' => $this->convertToOrderCurrency($this->getQuoteTotals()->getCatalogTotalPrice()),
                        'base_value' => $this->getQuoteTotals()->getCatalogTotalPrice(),
                        'label' => __('Catalog Total Price'),
                    ]
                );
            }

            if ($this->getNegotiatedDiscount()) {
                $this->catalogTotals['negotiated_discount'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'negotiated_discount',
                        'value' => $this->convertToOrderCurrency(-$this->getNegotiatedDiscount()),
                        'base_value' => -$this->getNegotiatedDiscount(),
                        'label' => __('Negotiated Discount'),
                    ]
                );
            }

            $parent = $this->getParentBlock();

            if ($this->isTaxDisplayedSalesSubtotalBoth()) {
                $parent = $this->getParentBlock();
                $this->catalogTotals['subtotal_incl'] = $parent->getTotal('subtotal_incl');
                $this->catalogTotals['subtotal_excl'] = $parent->getTotal('subtotal_excl');
                $parent->removeTotal('subtotal_excl');
                $parent->removeTotal('subtotal_incl');
                $parent->addTotalBefore($total, 'shipping');
            } else {
                $this->catalogTotals['subtotal'] = $parent->getTotal('subtotal');
                $parent->removeTotal('subtotal');
                $parent->addTotalBefore($total, 'shipping');
            }
        }

        return $this;
    }

    /**
     * Convert price from base currency to order currency.
     *
     * @param float $price
     * @return float
     */
    private function convertToOrderCurrency($price)
    {
        if ($this->getOrder()->getBaseCurrencyCode() != $this->getOrder()->getOrderCurrencyCode()) {
            $price *= $this->getOrder()->getBaseToOrderRate();
        }
        return $price;
    }

    /**
     * Get totals array for visualization.
     *
     * @return array
     */
    public function getCatalogTotals()
    {
        return $this->catalogTotals;
    }

    /**
     * Retrieve quote model object.
     *
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    private function getQuote()
    {
        if (!$this->quote) {
            $quoteId = $this->getOrder()->getQuoteId();

            if ($quoteId) {
                try {
                    $this->quote = $this->quoteRepository->get($quoteId, ['*']);
                } catch (NoSuchEntityException $e) {
                    $this->quote = null;
                }
            }
        }

        return $this->quote;
    }

    /**
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|null
     */
    protected function getNegotiableQuote()
    {
        if (!$this->negotiableQuote) {
            $negotiableQuote = null;
            $quoteExtensionAttributes = null;

            if ($this->getQuote()) {
                $quoteExtensionAttributes = $this->getQuote()->getExtensionAttributes();
            }

            if ($quoteExtensionAttributes && $quoteExtensionAttributes->getNegotiableQuote()) {
                $negotiableQuote = $quoteExtensionAttributes->getNegotiableQuote();
            }

            $this->negotiableQuote = $negotiableQuote;
        }

        return $this->negotiableQuote;
    }

    /**
     * @return bool
     */
    public function isNegotiableQuote()
    {
        return $this->getNegotiableQuote() !== null &&
        ($this->getNegotiableQuote()->getShippingPrice() !== null ||
            $this->getNegotiableQuote()->getNegotiatedPriceValue() !== null);
    }

    /**
     * Is tax included to grand total value.
     *
     * @return bool
     */
    private function isTaxDisplayedWithGrandTotal()
    {
        return $this->taxConfig->displaySalesTaxWithGrandTotal($this->_storeManager->getStore());
    }

    /**
     * Is tax included to grand total value.
     *
     * @return bool
     */
    private function isTaxDisplayedSalesSubtotalInclTax()
    {
        return $this->taxConfig->displaySalesSubtotalInclTax($this->_storeManager->getStore());
    }

    /**
     * Is tax included to grand total value.
     *
     * @return bool
     */
    private function isTaxDisplayedSalesSubtotalExclTax()
    {
        return $this->taxConfig->displaySalesSubtotalExclTax($this->_storeManager->getStore());
    }

    /**
     * Is tax included to grand total value.
     *
     * @return bool
     */
    private function isTaxDisplayedSalesSubtotalBoth()
    {
        return $this->taxConfig->displaySalesSubtotalBoth($this->_storeManager->getStore());
    }

    /**
     * Returns negotiated discount.
     *
     * @return float
     */
    private function getNegotiatedDiscount()
    {
        return $this->getQuoteTotals()->getCatalogTotalPrice() - $this->getSubtotal();
    }

    /**
     * Returns subtotal.
     *
     * @return float
     */
    private function getSubtotal()
    {
        return $this->isTaxDisplayedWithGrandTotal() ?
            $this->getOrder()->getBaseSubtotalInclTax() :
            $this->getOrder()->getBaseSubtotal();
    }

    /**
     * Get quote totals.
     *
     * @return \Magento\NegotiableQuote\Model\Quote\Totals
     */
    private function getQuoteTotals()
    {
        if (!$this->quoteTotals) {
            $this->quoteTotals = $this->quoteTotalsFactory->create(['quote' => $this->getQuote()]);
        }

        return $this->quoteTotals;
    }

    /**
     * Retrieve current order model instance.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Display prices from total object.
     *
     * @param \Magento\Framework\DataObject $total
     * @return string
     */
    public function displayPrice(\Magento\Framework\DataObject $total)
    {
        $basePrice = $total->getBaseValue() ? $total->getBaseValue() : $total->getValue();
        $priceString = $this->priceCurrency->format(
            $basePrice,
            true,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $this->getOrder()->getBaseCurrencyCode()
        );
        if ($this->getOrder()->getBaseCurrencyCode() != $this->getOrder()->getOrderCurrencyCode()) {
            $orderPrice = $this->priceCurrency->format(
                $total->getValue(),
                true,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $this->getOrder()->getOrderCurrencyCode()
            );
            $priceString .= '<br />[' . $orderPrice . ']';
        }

        return $priceString;
    }
}
