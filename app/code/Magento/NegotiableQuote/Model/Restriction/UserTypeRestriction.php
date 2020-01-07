<?php

namespace Magento\NegotiableQuote\Model\Restriction;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Restriction class for retrieve restrictions by user type from user context.
 */
class UserTypeRestriction implements RestrictionInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface[]
     */
    private $restrictions;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @param UserContextInterface $userContext
     * @param RestrictionInterface[] $restrictions
     */
    public function __construct(
        UserContextInterface $userContext,
        array $restrictions
    ) {
        $this->userContext = $userContext;
        $this->restrictions = $restrictions;
    }

    /**
     * Retrieve restriction object for current user type.
     *
     * @return RestrictionInterface|null
     */
    private function getRestriction()
    {
        $userType = $this->userContext->getUserType();
        if (!empty($this->restrictions[$userType])) {
            return $this->restrictions[$userType];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function canSubmit()
    {
        return $this->getRestriction() ? $this->getRestriction()->canSubmit() : false;
    }

    /**
     * @inheritdoc
     */
    public function canDuplicate()
    {
        return $this->getRestriction() ? $this->getRestriction()->canDuplicate() : false;
    }

    /**
     * @inheritdoc
     */
    public function canClose()
    {
        return $this->getRestriction() ? $this->getRestriction()->canClose() : false;
    }

    /**
     * @inheritdoc
     */
    public function canProceedToCheckout()
    {
        return $this->getRestriction() ? $this->getRestriction()->canProceedToCheckout() : false;
    }

    /**
     * @inheritdoc
     */
    public function canDelete()
    {
        return $this->getRestriction() ? $this->getRestriction()->canDelete() : false;
    }

    /**
     * @inheritdoc
     */
    public function canDecline()
    {
        return $this->getRestriction() ? $this->getRestriction()->canDecline() : false;
    }

    /**
     * @inheritdoc
     */
    public function canCurrencyUpdate()
    {
        return $this->getRestriction() ? $this->getRestriction()->canCurrencyUpdate() : false;
    }

    /**
     * @inheritdoc
     */
    public function isLockMessageDisplayed()
    {
        return $this->getRestriction() ? $this->getRestriction()->isLockMessageDisplayed() : true;
    }

    /**
     * @inheritdoc
     */
    public function isExpiredMessageDisplayed()
    {
        return $this->getRestriction() ? $this->getRestriction()->isExpiredMessageDisplayed() : false;
    }

    /**
     * @inheritdoc
     */
    public function isOwner()
    {
        return $this->getRestriction() ? $this->getRestriction()->isOwner() : false;
    }

    /**
     * @inheritdoc
     */
    public function isSubUserContent()
    {
        return $this->getRestriction() ? $this->getRestriction()->isSubUserContent() : false;
    }

    /**
     * @inheritdoc
     */
    public function setQuote(CartInterface $quote)
    {
        if ($this->getRestriction()) {
            $this->getRestriction()->setQuote($quote);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAllowed($resource)
    {
        return $this->getRestriction() ? $this->getRestriction()->isAllowed($resource) : false;
    }

    /**
     * @inheritdoc
     */
    public function isExtensionEnable()
    {
        return $this->getRestriction() ? $this->getRestriction()->isExtensionEnable() : false;
    }
}
