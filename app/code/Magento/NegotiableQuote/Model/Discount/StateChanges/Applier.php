<?php

namespace Magento\NegotiableQuote\Model\Discount\StateChanges;

use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Framework\App\State;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class is responsible for setting price changes flags in quote.
 */
class Applier
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param State $appState
     * @param UserContextInterface $userContext
     */
    public function __construct(
        State $appState,
        UserContextInterface $userContext
    ) {
        $this->appState = $appState;
        $this->userContext = $userContext;
    }

    /**
     * Set items changed discount flag to quote.
     *
     * @param CartInterface $quote
     * @return Applier
     */
    public function setHasItemChanges(CartInterface $quote)
    {
        return $this->setChanges($quote, NegotiableQuoteInterface::ITEMS_CHANGED);
    }

    /**
     * Set discount changed flag to quote.
     *
     * @param CartInterface $quote
     * @return Applier
     */
    public function setIsDiscountChanged(CartInterface $quote)
    {
        return $this->setChanges($quote, NegotiableQuoteInterface::DISCOUNT_CHANGED);
    }

    /**
     * Set discount removed limit flag to quote.
     *
     * @param CartInterface $quote
     * @return Applier
     */
    public function setIsDiscountRemovedLimit(CartInterface $quote)
    {
        return $this->setChanges($quote, NegotiableQuoteInterface::DISCOUNT_LIMIT);
    }

    /**
     * Set discount removed flag to quote.
     *
     * @param CartInterface $quote
     * @return Applier
     */
    public function setIsDiscountRemoved(CartInterface $quote)
    {
        return $this->setChanges($quote, NegotiableQuoteInterface::DISCOUNT_REMOVED);
    }

    /**
     * Set taxes changed flag to quote.
     *
     * @param CartInterface $quote
     * @return Applier
     */
    public function setIsTaxChanged(CartInterface $quote)
    {
        return $this->setChanges($quote, NegotiableQuoteInterface::TAX_CHANGED);
    }

    /**
     * Set address changed flag to quote.
     *
     * @param CartInterface $quote
     * @return Applier
     */
    public function setIsAddressChanged(CartInterface $quote)
    {
        return $this->setChanges($quote, NegotiableQuoteInterface::ADDRESS_CHANGED);
    }

    /**
     * Set message codes for store front and/or admin areas.
     *
     * @param CartInterface $quote
     * @param int $modificationType
     * @return $this
     */
    private function setChanges(CartInterface $quote, $modificationType)
    {
        $negotiableQuote = ($quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null)
            ? $quote->getExtensionAttributes()->getNegotiableQuote()
            : null;

        if ($negotiableQuote !== null) {
            if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_ADMIN
                || $this->userContext->getUserType() == UserContextInterface::USER_TYPE_INTEGRATION) {
                $modificationType *= NegotiableQuoteInterface::DISCOUNT_ADMIN_MODE;
            }
            $this->setMessageCode($negotiableQuote, $modificationType);
            if ($relatedCode = $this->getRelatedMessageCode($negotiableQuote, $modificationType)) {
                $this->setMessageCode($negotiableQuote, $relatedCode);
            }
        }

        return $this;
    }

    /**
     * Set message code to the negotiable quote.
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param int $modificationType
     * @return void
     */
    private function setMessageCode(NegotiableQuoteInterface $negotiableQuote, $modificationType)
    {
        if (!($negotiableQuote->getNotifications() & $modificationType)) {
            $negotiableQuote->setNotifications(
                $negotiableQuote->getNotifications()
                + $modificationType
            );
        }
    }

    /**
     * Retrieve message code.
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param int $code
     * @return int
     */
    private function getRelatedMessageCode(NegotiableQuoteInterface $negotiableQuote, $code)
    {
        $adminMode = NegotiableQuoteInterface::DISCOUNT_ADMIN_MODE;
        $relatedCode = floor($code / $adminMode) + ($code % $adminMode) * $adminMode;
        $notRelatedCode = [
            NegotiableQuoteInterface::ITEMS_CHANGED * NegotiableQuoteInterface::DISCOUNT_ADMIN_MODE,
            NegotiableQuoteInterface::ITEMS_CHANGED,
            NegotiableQuoteInterface::DISCOUNT_CHANGED,
            NegotiableQuoteInterface::DISCOUNT_REMOVED,
            NegotiableQuoteInterface::DISCOUNT_LIMIT,
        ];
        if ($negotiableQuote->getNegotiatedPriceValue() !== null && in_array($relatedCode, $notRelatedCode)) {
            $relatedCode = 0;
        }

        return $relatedCode;
    }

    /**
     * Remove message from the negotiable quote.
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param int $modificationType
     * @param bool $withArea [optional]
     * @return void
     */
    public function removeMessage(NegotiableQuoteInterface $negotiableQuote, $modificationType, $withArea = false)
    {
        if ($withArea && $this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $modificationType *= NegotiableQuoteInterface::DISCOUNT_ADMIN_MODE;
        }

        if ($negotiableQuote->getNotifications() & $modificationType) {
            $negotiableQuote->setNotifications(
                $negotiableQuote->getNotifications()
                - $modificationType
            );
        }
    }
}
