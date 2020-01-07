<?php

namespace Magento\NegotiableQuote\Block\Quote;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Helper\Quote as NegotiableQuoteHelper;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Base block for quote blocks.
 */
abstract class AbstractQuote extends Template
{
    /**
     * @var CartInterface
     */
    protected $quote;

    /**
     * @var CartInterface
     */
    protected $snapshotQuote;

    /**
     * @var NegotiableQuoteHelper
     */
    protected $negotiableQuoteHelper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;

    /**
     * @var NegotiableQuoteInterface
     */
    protected $negotiableQuote;

    /**
     * @param TemplateContext $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param NegotiableQuoteHelper $negotiableQuoteHelper
     * @param array $data [optional]
     */
    public function __construct(
        TemplateContext $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        NegotiableQuoteHelper $negotiableQuoteHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->postDataHelper = $postDataHelper;
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
    }

    /**
     * Retrieve current quote.
     *
     * @param bool $snapshot [optional]
     * @return CartInterface|null
     */
    public function getQuote($snapshot = false)
    {
        return $this->negotiableQuoteHelper->resolveCurrentQuote($snapshot);
    }

    /**
     * Get negotiable quote.
     *
     * @return NegotiableQuoteInterface|null
     */
    protected function getNegotiableQuote()
    {
        if (!$this->negotiableQuote
            && $this->getQuote()
            && $this->getQuote()->getExtensionAttributes()
            && $this->getQuote()->getExtensionAttributes()->getNegotiableQuote()) {
            $this->negotiableQuote = $this->getQuote(true)->getExtensionAttributes()->getNegotiableQuote();
        }
        return $this->negotiableQuote;
    }

    /**
     * Returns whether the quote is editable.
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->negotiableQuoteHelper->isSubmitAvailable();
    }

    /**
     * Returns if quote has changes.
     *
     * @return bool
     */
    public function hasQuoteChanges()
    {
        $hasChanges = false;
        if ($this->getNegotiableQuote()) {
            $hasChanges = $this->getNegotiableQuote()->getHasUnconfirmedChanges();
        }
        return $hasChanges;
    }

    /**
     * Returns if there are unconfirmed changes in quote.
     *
     * @return bool
     */
    public function hasUnconfirmedChanges()
    {
        $hasChanges = false;
        if ($this->getNegotiableQuote()) {
            $hasChanges = $this->getNegotiableQuote()->getIsCustomerPriceChanged();
        }
        return $hasChanges;
    }
}
