<?php

namespace Magento\NegotiableQuote\Model\Restriction;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Interface ActionInterface
 */
interface RestrictionInterface
{
    /**#@+
     * Constants
     */
    const ACTION_DUPLICATE = 'duplicate';
    const ACTION_CLOSE = 'close';
    const ACTION_SUBMIT = 'submit';
    const ACTION_PROCEED_TO_CHECKOUT = 'proceed_to_checkout';
    const ACTION_DELETE = 'delete';
    const ACTION_DECLINE = 'decline';
    /**#@-*/

    /**
     * Check submit availability
     *
     * @return bool
     */
    public function canSubmit();

    /**
     * Check duplicate availability
     *
     * @return bool
     */
    public function canDuplicate();

    /**
     * Check close availability
     *
     * @return bool
     */
    public function canClose();

    /**
     * Check proceed to checkout availability
     *
     * @return bool
     */
    public function canProceedToCheckout();

    /**
     * Check delete availability
     *
     * @return bool
     */
    public function canDelete();

    /**
     * Check decline availability
     *
     * @return bool
     */
    public function canDecline();

    /**
     * Check currency update availability.
     *
     * @return bool
     */
    public function canCurrencyUpdate();

    /**
     * Is lock message displayed
     *
     * @return bool
     */
    public function isLockMessageDisplayed();

    /**
     * Is expired message displayed
     *
     * @return bool
     */
    public function isExpiredMessageDisplayed();

    /**
     * Is action performed by quote owner
     *
     * @return bool
     */
    public function isOwner();

    /**
     * Is action performed on subuser content
     *
     * @return bool
     */
    public function isSubUserContent();

    /**
     * Set quote
     *
     * @param CartInterface $quote
     * @return $this
     */
    public function setQuote(CartInterface $quote);

    /**
     * Check current user permission on resource.
     *
     * @param string $resource
     * @return bool
     */
    public function isAllowed($resource);

    /**
     * Check is quote extension enabled.
     *
     * @return bool
     */
    public function isExtensionEnable();
}
