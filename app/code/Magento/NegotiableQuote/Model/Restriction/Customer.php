<?php

namespace Magento\NegotiableQuote\Model\Restriction;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;

/**
 * Class Customer
 */
class Customer extends AbstractRestriction
{
    /**
     * {@inheritdoc}
     */
    public function isOwner()
    {
        return $this->getQuote()->getCustomer()->getId() === $this->userContext->getUserId();
    }

    /**
     * {@inheritdoc}
     */
    public function isSubUserContent()
    {
        $parentId = $this->userContext->getUserId();
        $childIds = $this->structure->getAllowedChildrenIds($parentId);
        $quoteOwnerId = $this->getQuote()->getCustomer()->getId();

        return in_array($quoteOwnerId, $childIds);
    }

    /**
     * {@inheritdoc}
     */
    public function isLockMessageDisplayed()
    {
        return parent::isLockMessageDisplayed() && $this->isOwner();
    }

    /**
     * Check is action is allowed
     *
     * @param string $actionName
     * @return bool
     */
    protected function isActionAllowed($actionName)
    {
        return $this->isExtensionEnable() && parent::isActionAllowed($actionName);
    }

    /**
     * {@inheritdoc}
     */
    protected $allowedActionsByStatus = [
        NegotiableQuote::STATUS_CREATED => [
            self::ACTION_SUBMIT,
            self::ACTION_DUPLICATE,
            self::ACTION_CLOSE,
        ],
        NegotiableQuote::STATUS_PROCESSING_BY_ADMIN => [
            self::ACTION_DUPLICATE,
            self::ACTION_CLOSE,
        ],
        NegotiableQuote::STATUS_PROCESSING_BY_CUSTOMER => [
            self::ACTION_SUBMIT,
            self::ACTION_DELETE,
            self::ACTION_DUPLICATE,
        ],
        NegotiableQuote::STATUS_SUBMITTED_BY_CUSTOMER => [
            self::ACTION_DUPLICATE,
            self::ACTION_CLOSE,
        ],
        NegotiableQuote::STATUS_SUBMITTED_BY_ADMIN => [
            self::ACTION_SUBMIT,
            self::ACTION_PROCEED_TO_CHECKOUT,
            self::ACTION_DELETE,
            self::ACTION_DUPLICATE,
            self::ACTION_CLOSE,
        ],
        NegotiableQuote::STATUS_ORDERED => [
            self::ACTION_DUPLICATE,
        ],
        NegotiableQuote::STATUS_EXPIRED => [
            self::ACTION_SUBMIT,
            self::ACTION_DELETE,
            self::ACTION_DUPLICATE,
            self::ACTION_PROCEED_TO_CHECKOUT,
        ],
        NegotiableQuote::STATUS_DECLINED => [
            self::ACTION_SUBMIT,
            self::ACTION_PROCEED_TO_CHECKOUT,
            self::ACTION_DELETE,
            self::ACTION_DUPLICATE,
        ],
        NegotiableQuote::STATUS_CLOSED => [
            self::ACTION_DUPLICATE,
            self::ACTION_DELETE,
        ],
    ];
}
