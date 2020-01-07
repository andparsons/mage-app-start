<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NotFoundException;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteShippingManagementInterface;
use Magento\NegotiableQuote\Model\Quote\History as QuoteHistory;
use Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;

/**
 * Class for setting shipping method for Negotiable Quote.
 */
class NegotiableQuoteShippingManagement implements NegotiableQuoteShippingManagementInterface
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory
     */
    private $validatorFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History
     */
    private $quoteHistory;

    /**
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param ValidatorInterfaceFactory $validatorFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param RestrictionInterface $restriction
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param QuoteHistory $quoteHistory
     */
    public function __construct(
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        ValidatorInterfaceFactory $validatorFactory,
        CartRepositoryInterface $quoteRepository,
        RestrictionInterface $restriction,
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        QuoteHistory $quoteHistory
    ) {
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
        $this->validatorFactory = $validatorFactory;
        $this->quoteRepository = $quoteRepository;
        $this->restriction = $restriction;
        $this->quoteHistory = $quoteHistory;
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
    }

    /**
     * @inheritdoc
     */
    public function setShippingMethod($quoteId, $shippingCode)
    {
        $quote = $this->retrieveNegotiableQuote($quoteId);
        $shippingRate = $this->retrieveShippingRate($quote, $shippingCode);
        $quote->getExtensionAttributes()
            ->getShippingAssignments()[0]
            ->getShipping()
            ->setMethod($shippingRate->getCode());
        $quote->getExtensionAttributes()
            ->getNegotiableQuote()
            ->setStatus(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
        $this->quoteRepository->save($quote);
        $this->quoteHistory->updateStatusLog($quote->getId(), true);

        return true;
    }

    /**
     * Validate quotes and return valid negotiable quotes in case no exception occurs.
     * Will throw exception in case in case of quote doesn't pass validation.
     *
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\InputException
     */
    private function retrieveNegotiableQuote($quoteId)
    {
        $messages = [];
        $validator = $this->validatorFactory->create(['action' => 'edit']);
        $quote = $this->negotiableQuoteManagement->getNegotiableQuote($quoteId);
        $this->restriction->setQuote($quote);
        $validateResult = $validator->validate(['quote' => $quote]);
        if ($validateResult->hasMessages()) {
            foreach ($validateResult->getMessages() as $message) {
                $messages[] = $message;
            }
        }
        if (!empty($messages)) {
            $exception = new InputException(
                __('Cannot obtain the requested data. You must fix the errors listed below first.')
            );
            foreach ($messages as $message) {
                $exception->addError($message);
            }
            throw $exception;
        }

        return $quote;
    }

    /**
     * Validate address and retrieve shipping rate by code for negotiable quote.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $shippingCode
     * @return \Magento\Quote\Model\Quote\Address\Rate
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function retrieveShippingRate(\Magento\Quote\Api\Data\CartInterface $quote, $shippingCode)
    {
        if ($quote->getIsVirtual()) {
            throw new \Magento\Framework\Exception\StateException(
                __('Shipping method cannot be set for a virtual quote.')
            );
        }
        $shippingAssignments = $quote->getExtensionAttributes()->getShippingAssignments();
        $shippingAssignment = is_array($shippingAssignments) ? array_shift($shippingAssignments) : null;
        if (!(
            $shippingAssignment
            && $shippingAssignment->getShipping()
            && $shippingAssignment->getShipping()->getAddress()
            && $shippingAssignment->getShipping()->getAddress()->getCountryId()
        )) {
            throw new \Magento\Framework\Exception\StateException(
                __('Cannot add the shipping method. You must add a shipping address into the quote first.')
            );
        }
        /* @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();
        $shippingRate = $shippingAddress->getShippingRateByCode($shippingCode);
        if (!$shippingRate) {
            throw new \Magento\Framework\Exception\NotFoundException(
                __(
                    'Requested shipping method is not found. Row ID: ShippingMethodID = %ShippingMethodID.',
                    ['ShippingMethodID' => $shippingCode]
                )
            );
        }

        return $shippingRate;
    }
}
