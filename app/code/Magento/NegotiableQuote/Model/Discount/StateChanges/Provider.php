<?php

namespace Magento\NegotiableQuote\Model\Discount\StateChanges;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\Status\LabelProviderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Provider
 */
class Provider
{
    /**
     * Negotiable quote manager
     *
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var LabelProviderInterface
     */
    protected $labelProvider;

    /**
     * @var Applier
     */
    private $messageApplier;

    /**
     * @var NegotiableQuoteInterface
     */
    private $negotiableQuote;

    /**
     * @var RestrictionInterface
     */
    protected $restriction;

    /**
     * Negotiable quote
     *
     * @var int
     */
    protected $discountModificationType;

    /**
     * Json Serializer instance
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\State $appState
     * @param LabelProviderInterface $labelProvider
     * @param Applier $messageApplier
     * @param RestrictionInterface $restriction
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\State $appState,
        LabelProviderInterface $labelProvider,
        Applier $messageApplier,
        RestrictionInterface $restriction,
        Json $serializer = null
    ) {
        $this->request = $request;
        $this->appState = $appState;
        $this->labelProvider = $labelProvider;
        $this->messageApplier = $messageApplier;
        $this->restriction = $restriction;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Quote discount was modified
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function hasChanges(CartInterface $quote)
    {
        return $this->hasItemChanges($quote)
            || $this->isDiscountChanged($quote)
            || $this->isDiscountRemoved($quote)
            || $this->isDiscountRemovedLimit($quote);
    }

    /**
     * Items in negotiable quote where changed
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function hasItemChanges(CartInterface $quote)
    {
        return $this->hasDiscountChanges($quote, NegotiableQuoteInterface::ITEMS_CHANGED);
    }

    /**
     * Quote discount was changed
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function isDiscountChanged(CartInterface $quote)
    {
        return $this->hasDiscountChanges($quote, NegotiableQuoteInterface::DISCOUNT_CHANGED);
    }

    /**
     * Quote discount was removed
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function isDiscountRemovedLimit(CartInterface $quote)
    {
        return $this->hasDiscountChanges($quote, NegotiableQuoteInterface::DISCOUNT_LIMIT);
    }

    /**
     * Quote discount was removed
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function isDiscountRemoved(CartInterface $quote)
    {
        return $this->hasDiscountChanges($quote, NegotiableQuoteInterface::DISCOUNT_REMOVED);
    }

    /**
     * Check is quote has discount type changes
     *
     * @param CartInterface $quote
     * @param int $type
     * @param bool $needRemove
     * @return bool
     */
    public function hasDiscountChanges(CartInterface $quote, $type, $needRemove = false)
    {
        if ($this->discountModificationType === null) {
            if ($quote !== null
                && $quote->getExtensionAttributes() !== null
                && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
            ) {
                $this->negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
                $this->discountModificationType = $quote->getExtensionAttributes()
                    ->getNegotiableQuote()
                    ->getNotifications();
            }
        }
        if ($this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $type *= NegotiableQuoteInterface::DISCOUNT_ADMIN_MODE;
        }

        if ($needRemove) {
            $this->messageApplier->removeMessage($this->negotiableQuote, $type);
        }

        return $this->discountModificationType ? ($this->discountModificationType & $type) === $type : false;
    }

    /**
     * @param CartInterface $quote
     * @return array
     */
    public function getChangesMessages(CartInterface $quote)
    {
        $messages = [];

        if ($this->negotiableQuoteExists($quote)) {
            return $messages;
        }

        if ($this->restriction->canSubmit()) {
            foreach ($this->labelProvider->getMessageLabels() as $key => $message) {
                if ($this->hasDiscountChanges($quote, $key, true)) {
                    $messages[] = $message;
                }
            }
        }

        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        if ($negotiableQuote && $negotiableQuote->getStatus() == NegotiableQuoteInterface::STATUS_EXPIRED) {
            $messages = [];
        }

        if ($removeMessage = $this->getRemovedSkuMessage($negotiableQuote)) {
            $messages[] = $removeMessage;
        }
        return $messages;
    }

    /**
     * Is negotiable quote exists
     *
     * @param CartInterface $quote
     * @return bool
     */
    private function negotiableQuoteExists(CartInterface $quote)
    {
        return $quote === null
        || $quote->getExtensionAttributes() === null
        || $quote->getExtensionAttributes()->getNegotiableQuote() === null;
    }

    /**
     * Get message about deleted products
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @return string
     */
    private function getRemovedSkuMessage(NegotiableQuoteInterface $negotiableQuote)
    {
        $deletedSkuArray = $negotiableQuote->getDeletedSku()
            ? $this->serializer->unserialize($negotiableQuote->getDeletedSku())
            : false;
        $state = $this->appState->getAreaCode();
        if (empty($deletedSkuArray) || empty($deletedSkuArray[$state])) {
            return '';
        }
        $isLocked = !$this->restriction->canSubmit();
        $isNegotiable = $negotiableQuote->getNegotiatedPriceValue() !== null;
        $message = $this->labelProvider
            ->getRemovedSkuMessage($deletedSkuArray[$state], $isNegotiable, $isLocked);

        $statusLocked = [NegotiableQuoteInterface::STATUS_CLOSED, NegotiableQuoteInterface::STATUS_ORDERED];
        if (!empty($deletedSkuArray[$state]) && !in_array($negotiableQuote->getStatus(), $statusLocked)) {
            $deletedSkuArray[$state] = [];
            $negotiableQuote->setDeletedSku($this->serializer->serialize($deletedSkuArray));
        }
        return $message;
    }
}
