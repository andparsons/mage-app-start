<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Shipping;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Block for displaying shipping method on negotiable quote page.
 *
 * @api
 * @since 100.0.0
 */
class Method extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var string
     */
    private $customShippingPriceField = 'custom_shipping_price';

    /**
     * @var array
     */
    private $ignoreStatus = [NegotiableQuoteInterface::STATUS_ORDERED, NegotiableQuoteInterface::STATUS_CLOSED];

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param CartRepositoryInterface $quoteRepository
     * @param RestrictionInterface $restriction
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        CartRepositoryInterface $quoteRepository,
        RestrictionInterface $restriction,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $taxData, $data);
        $this->quoteRepository = $quoteRepository;
        $this->restriction = $restriction;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * Retrieve quote model object.
     *
     * @return \Magento\Quote\Model\Quote|null
     */
    public function getQuote()
    {
        if (!$this->quote) {
            $quoteId = $this->getRequest()->getParam('quote_id');

            if ($quoteId) {
                try {
                    $this->quote = $this->quoteRepository->get($quoteId, ['*']);
                    if ($this->quote->getExtensionAttributes()
                        && $this->quote->getExtensionAttributes()->getNegotiableQuote()
                        && $this->quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
                        && !in_array(
                            $this->quote->getExtensionAttributes()->getNegotiableQuote()->getStatus(),
                            $this->ignoreStatus
                        )
                    ) {
                        $this->quote->getShippingAddress()->setCollectShippingRates(true);
                        $this->totalsCollector->collectAddressTotals($this->quote, $this->quote->getShippingAddress());
                        $this->quote->getShippingAddress()->collectShippingRates();
                    }
                } catch (NoSuchEntityException $e) {
                    $this->quote = null;
                }
            }
        }

        return $this->quote;
    }

    /**
     * @return string
     */
    public function getShippingMethodUrl()
    {
        $quote = $this->getQuote();
        if ($quote) {
            return $this->getUrl('*/*/shippingMethod/', ['quote_id' => $quote->getId()]);
        }
        return '';
    }

    /**
     * Can edit.
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->restriction->canSubmit();
    }

    /**
     * Get shipping price.
     *
     * @return float|null
     */
    public function getProposedShippingPrice()
    {
        if ($this->getRequest()->getParam('isAjax')
            && $this->getRequest()->getParam($this->customShippingPriceField) !== null
        ) {
            return $this->getRequest()->getParam($this->customShippingPriceField);
        }
        $shippingPrice = ($this->getQuote()
            && $this->getQuote()->getExtensionAttributes()
            && $this->getQuote()->getExtensionAttributes()->getNegotiableQuote())
            ? $this->getQuote()->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice()
            : null;

        return $shippingPrice;
    }

    /**
     * Get currency symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol(null, $this->getQuote()->getCurrency()->getBaseCurrencyCode());
    }

    /**
     * Get original shipping method price.
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $rate
     * @param bool $flag
     * @return float
     */
    public function getOriginalShippingPrice(\Magento\Quote\Model\Quote\Address\Rate $rate, $flag)
    {
        $price = $rate->getOriginalShippingPrice() !== null ? $rate->getOriginalShippingPrice() : $rate->getPrice();

        return $this->getShippingPrice($price, $flag);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->priceCurrency->format(
            $this->_taxData->getShippingPrice(
                $price,
                $flag,
                $this->getAddress(),
                null,
                $this->getAddress()->getQuote()->getStore()
            ),
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore(),
            $this->getQuote()->getBaseCurrencyCode()
        );
    }
}
