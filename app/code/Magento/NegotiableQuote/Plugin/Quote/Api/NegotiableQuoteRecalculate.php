<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Api;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote as NegotiableQuoteResource;

/**
 * Plugin for negotiable quote recalculation after quote was saved in WebAPI.
 */
class NegotiableQuoteRecalculate
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * @var NegotiableQuoteInterfaceFactory
     */
    private $negotiableQuoteFactory;

    /**
     * @var NegotiableQuoteResource
     */
    private $negotiableQuoteResource;

    /**
     * @var array
     */
    private $quotesForRecalculate = [];

    /**
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param NegotiableQuoteInterfaceFactory $negotiableQuoteFactory
     * @param NegotiableQuoteResource $negotiableQuoteResource
     */
    public function __construct(
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        NegotiableQuoteInterfaceFactory $negotiableQuoteFactory,
        NegotiableQuoteResource $negotiableQuoteResource
    ) {
        $this->quoteItemManagement = $quoteItemManagement;
        $this->negotiableQuoteFactory = $negotiableQuoteFactory;
        $this->negotiableQuoteResource = $negotiableQuoteResource;
    }

    /**
     * Check if quote needs recalculation.
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        CartRepositoryInterface $subject,
        CartInterface $quote
    ) {
        if (!isset($this->quotesForRecalculate[$quote->getId()])) {
            $this->quotesForRecalculate[$quote->getId()] = false;
            if ($quote && $quote->getExtensionAttributes()
                && $quote->getExtensionAttributes()->getNegotiableQuote()
            ) {
                $this->quotesForRecalculate[$quote->getId()] = $this->quoteHasChanges($quote);
            }
        }

        return [$quote];
    }

    /**
     * Check if negotiable quote has changes in fields that require recalculate.
     *
     * @param CartInterface $quote
     * @return bool
     */
    private function quoteHasChanges(CartInterface $quote)
    {
        $negotiableQuoteOld = $this->negotiableQuoteFactory->create();
        $this->negotiableQuoteResource->load($negotiableQuoteOld, $quote->getId());
        if (!$negotiableQuoteOld->getQuoteId() || !$negotiableQuoteOld->getIsRegularQuote()) {
            return false;
        }
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $fieldsCheck = [
            NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE,
            NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE
        ];
        foreach ($fieldsCheck as $field) {
            if ($negotiableQuote->hasData($field)
                && $negotiableQuote->getData($field) != $negotiableQuoteOld->getData($field)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recalculate negotiable quote after quote save.
     *
     * @param CartRepositoryInterface $subject
     * @param null $result
     * @param CartInterface $quote
     * @return CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CartRepositoryInterface $subject,
        $result,
        CartInterface $quote
    ) {
        if (!empty($this->quotesForRecalculate[$quote->getId()])) {
            $this->quotesForRecalculate[$quote->getId()] = false;
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            if ($negotiableQuote->getNegotiatedPriceValue() === null) {
                $this->quoteItemManagement->recalculateOriginalPriceTax($quote->getId(), true, true);
            } else {
                $this->quoteItemManagement->updateQuoteItemsCustomPrices($quote->getId());
            }
        }

        return $result;
    }
}
