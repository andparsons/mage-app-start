<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals;

use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\NegotiableQuote\Helper\Quote as NegotiableQuoteHelper;

/**
 * Class AbstractTotals.
 */
class AbstractTotals extends \Magento\NegotiableQuote\Block\Quote\AbstractQuote
{
    /**
     * @var string
     */
    protected $code = '';

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param TemplateContext $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param NegotiableQuoteHelper $negotiableQuoteHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        NegotiableQuoteHelper $negotiableQuoteHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $negotiableQuoteHelper, $data);
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get total value.
     *
     * @return \Magento\Framework\DataObject
     */
    public function getTotal()
    {
        $totals = $this->getParentBlock()->getTotals();

        return $totals[$this->code];
    }

    /**
     * Display prices.
     *
     * @param float $price
     * @return string
     */
    public function displayPrices($price = null)
    {
        $displayedPrice = '';

        if ($price !== null) {
            $displayedPrice = number_format($price, 2, '.', '');
        }

        return $displayedPrice;
    }

    /**
     * Get formatted price value including currency.
     *
     * @param float $price
     * @param string $code
     * @return string
     */
    public function formatPrice($price, $code = null)
    {
        return $this->negotiableQuoteHelper->formatPrice($price, $code);
    }

    /**
     * Retrieve currency symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol(null, $this->getQuote()->getCurrency()->getBaseCurrencyCode());
    }
}
