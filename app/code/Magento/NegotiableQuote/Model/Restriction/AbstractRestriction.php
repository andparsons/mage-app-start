<?php

namespace Magento\NegotiableQuote\Model\Restriction;

use Magento\Framework\Exception\StateException;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;

/**
 * Class AbstractRestriction
 */
abstract class AbstractRestriction implements RestrictionInterface
{
    /**
     * Quote
     *
     * @var CartInterface
     */
    protected $quote;

    /**
     * Quote status
     *
     * @var string
     */
    protected $quoteStatus;

    /**
     * Allowed actions for statuses
     *
     * @var array
     */
    protected $allowedActionsByStatus = [];

    /**
     * Lock message on quote page
     *
     * @var string
     */
    protected $lockMessage = '';

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    protected $structure;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Magento\NegotiableQuote\Model\Config
     */
    protected $config;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Company\Model\Company\Structure $structure
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\NegotiableQuote\Model\Config $config
     * @param CartInterface $quote
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Company\Model\Company\Structure $structure,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\NegotiableQuote\Model\Config $config,
        CartInterface $quote
    ) {
        $this->userContext = $userContext;
        $this->structure = $structure;
        $this->authorization = $authorization;
        $this->config = $config;
        $this->quote = $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function canSubmit()
    {
        return $this->isActionAllowed(self::ACTION_SUBMIT) && $this->isOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function canDuplicate()
    {
        return $this->isActionAllowed(self::ACTION_DUPLICATE) && $this->isOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function canClose()
    {
        return $this->isActionAllowed(self::ACTION_CLOSE) && $this->isOwner()
        || $this->userContext->getUserType() !== \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER;
    }

    /**
     * {@inheritdoc}
     */
    public function canProceedToCheckout()
    {
        return $this->isActionAllowed(self::ACTION_PROCEED_TO_CHECKOUT) && $this->isOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function canDelete()
    {
        return $this->isActionAllowed(self::ACTION_DELETE) && $this->isOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function canDecline()
    {
        return $this->isActionAllowed(self::ACTION_DECLINE) && $this->isOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function isLockMessageDisplayed()
    {
        return !$this->canSubmit()
        && !in_array($this->getQuoteStatus(), [NegotiableQuote::STATUS_CLOSED, NegotiableQuote::STATUS_ORDERED]);
    }

    /**
     * {@inheritdoc}
     */
    public function isExpiredMessageDisplayed()
    {
        return $this->getQuoteStatus() == NegotiableQuote::STATUS_EXPIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function canCurrencyUpdate()
    {
        return $this->getQuoteStatus() != NegotiableQuote::STATUS_CLOSED
            && $this->getQuoteStatus() != NegotiableQuote::STATUS_ORDERED;
    }

    /**
     * {@inheritdoc}
     */
    public function isOwner()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSubUserContent()
    {
        return false;
    }

    /**
     * Check is action is allowed
     *
     * @param string $actionName
     * @return bool
     */
    protected function isActionAllowed($actionName)
    {
        $status = $this->getQuoteStatus();
        $allowedActions = $this->allowedActionsByStatus;

        return isset($allowedActions[$status]) && in_array($actionName, $allowedActions[$status]);
    }

    /**
     * Get quote status
     *
     * @return string
     */
    protected function getQuoteStatus()
    {
        if (!$this->quoteStatus) {
            $this->quoteStatus = '';
            $quoteExtensionAttributes = $this->getQuote() ? $this->getQuote()->getExtensionAttributes() : null;

            if ($quoteExtensionAttributes && $quoteExtensionAttributes->getNegotiableQuote()) {
                $this->quoteStatus = $quoteExtensionAttributes->getNegotiableQuote()->getStatus();
            }
        }

        return $this->quoteStatus;
    }

    /**
     * Get quote
     * @return CartInterface
     * @throws StateException
     */
    protected function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set quote
     *
     * @param CartInterface $quote
     * @return $this
     */
    public function setQuote(CartInterface $quote)
    {
        $this->quote = $quote;
        $this->quoteStatus = '';
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($resource)
    {
        return $this->authorization->isAllowed($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isExtensionEnable()
    {
        return $this->config->isActive(ScopeInterface::SCOPE_STORE, $this->getQuote()->getStoreId());
    }
}
