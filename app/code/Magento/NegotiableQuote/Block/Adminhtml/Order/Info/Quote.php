<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Order\Info;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Quote block
 *
 * @api
 * @since 100.0.0
 */
class Quote extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface
     */
    private $negotiableQuote;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteRepository = $quoteRepository;
        $this->registry = $registry;
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * Retrieve quote model object
     *
     * @return CartInterface|null
     */
    protected function getQuote()
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
    private function getNegotiableQuote()
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
     * Get url for quote
     *
     * @return string
     */
    public function getViewQuoteUrl()
    {
        return $this->getUrl('quotes/quote/view/', ['quote_id' => $this->getOrder()->getQuoteId()]);
    }

    /**
     * Surround quote id with accompanying symbols
     *
     * @return string
     */
    public function getViewQuoteLabel()
    {
        return '#' . $this->getOrder()->getQuoteId() . ': ';
    }

    /**
     * Retrieve negotiable quote name
     *
     * @return string|null
     */
    public function getQuoteName()
    {
        $quoteName = null;

        $negotiableQuote = $this->getNegotiableQuote();
        if ($negotiableQuote && $negotiableQuote->getQuoteName()) {
            $quoteName = $negotiableQuote->getQuoteName();
        }

        return $quoteName;
    }

    /**
     * @return bool
     */
    public function isNegotiableQuote()
    {
        return $this->getNegotiableQuote() !== null &&
        $this->getNegotiableQuote()->getQuoteId() !== null;
    }
}
