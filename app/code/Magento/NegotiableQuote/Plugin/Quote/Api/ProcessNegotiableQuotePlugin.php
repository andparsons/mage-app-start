<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Api;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteConverter;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Plugin for negotiable quote processing for get and get list methods in WebAPI.
 */
class ProcessNegotiableQuotePlugin
{
    /**
     * User session.
     *
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteConverter
     */
    private $negotiableQuoteConverter;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var int[]
     */
    private $updatedQuoteIds = [];

    /**
     * @var \Magento\Quote\Api\Data\CartInterface[]
     */
    private $snapshotQuote = [];

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param RestrictionInterface $restriction
     * @param NegotiableQuoteConverter $negotiableQuoteConverter
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        RestrictionInterface $restriction,
        NegotiableQuoteConverter $negotiableQuoteConverter,
        SerializerInterface $serializer
    ) {
        $this->userContext = $userContext;
        $this->quoteItemManagement = $quoteItemManagement;
        $this->restriction = $restriction;
        $this->negotiableQuoteConverter = $negotiableQuoteConverter;
        $this->serializer = $serializer;
    }

    /**
     * Process negotiable quote for get.
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        CartRepositoryInterface $subject,
        CartInterface $result
    ) {
        return $this->processQuote($result);
    }

    /**
     * Process negotiable quote for get list.
     *
     * @param CartRepositoryInterface $subject
     * @param \Magento\Framework\Api\SearchResultsInterface $result
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        CartRepositoryInterface $subject,
        \Magento\Framework\Api\SearchResultsInterface $result
    ) {
        $items = [];
        foreach ($result->getItems() as $item) {
            $items[] = $this->processQuote($item);
        }
        $result->setItems($items);

        return $result;
    }

    /**
     * Process negotiable quote for data actualization.
     * Replace quote to snapshot if negotiable quote is locked. Recalculate prices if quote is available for editing.
     *
     * @param CartInterface $quote
     * @return CartInterface
     */
    private function processQuote(CartInterface $quote)
    {
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
            && $this->userContext->getUserType() != UserContextInterface::USER_TYPE_CUSTOMER
        ) {
            $this->restriction->setQuote($quote);
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $quoteId = $quote->getId();
            if (!$this->restriction->canSubmit()) {
                if (empty($this->snapshotQuote[$quoteId]) && $negotiableQuote->getSnapshot()) {
                    $snapshot = $this->serializer->unserialize($negotiableQuote->getSnapshot());
                    $this->snapshotQuote[$quoteId] = $this->negotiableQuoteConverter->arrayToQuote($snapshot);
                } else {
                    $this->snapshotQuote[$quoteId] = $quote;
                }
                return $this->snapshotQuote[$quoteId];
            }
            $this->updateQuoteItemPrices($quote);
        }

        return $quote;
    }

    /**
     * Recalculate prices if quote is available for editing.
     *
     * @param CartInterface $quote
     * @return void
     */
    private function updateQuoteItemPrices(CartInterface $quote)
    {
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $quoteId = $quote->getId();
        if (!in_array($quoteId, $this->updatedQuoteIds)) {
            $this->updatedQuoteIds[] = $quoteId;
            if ($negotiableQuote->getNegotiatedPriceValue() === null) {
                $this->quoteItemManagement->recalculateOriginalPriceTax($quote->getId(), true, true, false, false);
            } else {
                $this->quoteItemManagement->updateQuoteItemsCustomPrices($quote->getId(), false);
            }
        }
    }
}
