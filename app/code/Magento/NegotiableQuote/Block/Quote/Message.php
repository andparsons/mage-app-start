<?php

namespace Magento\NegotiableQuote\Block\Quote;

use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class Message
 *
 * @api
 * @since 100.0.0
 */
class Message extends Template
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @param TemplateContext $context
     * @param QuoteHelper $quoteHelper
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        QuoteHelper $quoteHelper,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteHelper = $quoteHelper;
        $this->restriction = $restriction;
        $this->initMessages();
    }

    /**
     * Get messages for quote.
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set additional message.
     * @param string $message
     * @return void
     */
    public function setAdditionalMessage($message)
    {
        if ($message) {
            $this->messages[] = $message;
        }
    }

    /**
     * Collect all messages for quote.
     * @return void
     */
    protected function initMessages()
    {
        if ($this->quoteHelper->isLockMessageDisplayed()) {
            $this->messages[] = __(
                'This quote is currently locked for editing. It will become available once released by the Merchant.'
            );
        }

        if ($this->quoteHelper->isExpiredMessageDisplayed()) {
            $this->messages[] = __(
                'Your quote has expired and the product prices have been updated as per the latest prices in your'
                . ' catalog. You can either re-submit the quote to seller for further negotiation or go to checkout.'
            );
        }

        if (!$this->restriction->isOwner()) {
            $this->messages[] = __(
                'You are not an owner of this quote. You cannot edit it or take any actions on it.'
            );
        }
    }
}
