<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Model\Discount\StateChanges\Applier;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class for managing quotes address.
 */
class Address
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History
     */
    private $quoteHistory;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier
     */
    private $messageApplier;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param TotalsCollector $totalsCollector
     * @param RestrictionInterface $restriction
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param History $quoteHistory
     * @param Applier $messageApplier
     * @param AddressRepositoryInterface $addressRepository
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        TotalsCollector $totalsCollector,
        RestrictionInterface $restriction,
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        History $quoteHistory,
        Applier $messageApplier,
        AddressRepositoryInterface $addressRepository,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->totalsCollector = $totalsCollector;
        $this->restriction = $restriction;
        $this->quoteItemManagement = $quoteItemManagement;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteHistory = $quoteHistory;
        $this->messageApplier = $messageApplier;
        $this->addressRepository = $addressRepository;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
    }

    /**
     * Update shipping address in quote.
     *
     * @param int $quoteId
     * @param AddressInterface $address
     * @return bool
     * @throws \Exception
     */
    public function updateQuoteShippingAddress($quoteId, \Magento\Customer\Api\Data\AddressInterface $address)
    {
        $quote = $this->negotiableQuoteManagement->getNegotiableQuote($quoteId);
        if (!$this->restriction->canSubmit()) {
            return false;
        }
        try {
            $taxData = $this->quoteHistory->collectTaxDataFromQuote($quote);
            $oldData = $this->quoteHistory->collectOldDataFromQuote($quote);
            $shippingAddress = $quote->getShippingAddress()->importCustomerAddressData($address);
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->save();

            if ($quote->isVirtual()) {
                $billingAddress = $quote->getBillingAddress()->importCustomerAddressData($address);
                $billingAddress->save();
            }
            $this->totalsCollector->collectQuoteTotals($quote);
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $needRecalculate = false;
            if ($negotiableQuote->getNegotiatedPriceValue() === null) {
                $needRecalculate = true;
            }
            $this->quoteItemManagement
                ->recalculateOriginalPriceTax($quoteId, $needRecalculate, $needRecalculate, false);

            $this->quoteHistory->checkPricesAndDiscounts($quote, $oldData);
            $resultTaxData = $this->quoteHistory->checkTaxes($quote, $taxData);
            if ($resultTaxData->getIsTaxChanged() || $resultTaxData->getIsShippingTaxChanged()) {
                $this->messageApplier->setIsTaxChanged($quote);
            } else {
                $this->messageApplier->setIsAddressChanged($quote);
            }
            $this->negotiableQuoteManagement->updateProcessingByCustomerQuoteStatus($quoteId);
        } catch (\Exception $e) {
            throw new \Exception(__('Unable to update shipping address'));
        }
        return true;
    }

    /**
     * Update shipping address draft in quote.
     *
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function updateQuoteShippingAddressDraft($quoteId)
    {
        $quote = $this->negotiableQuoteManagement->getNegotiableQuote($quoteId);
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $snapshot = json_decode($negotiableQuote->getSnapshot(), true);
        if (!$this->restriction->canProceedToCheckout()
            || !is_array($snapshot)
            || !$negotiableQuote->getIsAddressDraft()
        ) {
            return $quote;
        }
        $shipping = $snapshot['shipping_address'];
        $billing = $snapshot['billing_address'];
        if ($this->checkAddressChanges($quote->getShippingAddress(), $shipping)) {
            $quote->removeAddress($quote->getShippingAddress()->getId());
            unset($shipping['address_id']);
            unset($shipping['entity_id']);
            $quote->getShippingAddress()->setData($shipping);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getExtensionAttributes()->setShippingAssignments(null);
        }
        if ($this->checkAddressChanges($quote->getBillingAddress(), $billing)) {
            $quote->removeAddress($quote->getBillingAddress()->getId());
            unset($billing['address_id']);
            unset($billing['entity_id']);
            $quote->getBillingAddress()->setData($billing);
            $quote->getBillingAddress()->setCollectShippingRates(true);
        }
        $this->totalsCollector->collectAddressTotals($quote, $quote->getShippingAddress());
        $negotiableQuote->setIsAddressDraft(false);
        $this->quoteRepository->save($quote);
        $isNeedRecalculate = $negotiableQuote->getNegotiatedPriceValue() === null;
        $this->quoteItemManagement->recalculateOriginalPriceTax($quoteId, $isNeedRecalculate, $isNeedRecalculate);
        return $quote;
    }

    /**
     * Check is address has changed data.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface|null $address
     * @param array $newAddress
     * @return bool
     */
    private function checkAddressChanges($address, array $newAddress)
    {
        if ($address && $newAddress) {
            return !empty(array_diff_assoc($newAddress, $address->getData()));
        }
        return false;
    }

    /**
     * Update quote address.
     *
     * @param int $quoteId
     * @param int $addressId
     * @return void
     */
    public function updateAddress($quoteId, $addressId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $taxData = $this->quoteHistory->collectTaxDataFromQuote($quote);
        $oldData = $this->quoteHistory->collectOldDataFromQuote($quote);

        $addressData = $this->addressRepository->getById($addressId);
        $address = $quote->getShippingAddress()->importCustomerAddressData($addressData);
        $address->setCollectShippingRates(true);
        $this->totalsCollector->collectAddressTotals($quote, $address);
        $address->save();
        $this->negotiableQuoteManagement->updateProcessingByCustomerQuoteStatus($quoteId, false);

        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $needRecalculate = false;
        if ($negotiableQuote->getNegotiatedPriceValue() === null) {
            $needRecalculate = true;
        }
        $this->quoteItemManagement
            ->recalculateOriginalPriceTax($quoteId, $needRecalculate, $needRecalculate, false);

        $this->quoteHistory->checkPricesAndDiscounts($quote, $oldData);
        $resultTaxData = $this->quoteHistory->checkTaxes($quote, $taxData);
        if ($resultTaxData->getIsTaxChanged() || $resultTaxData->getIsShippingTaxChanged()) {
            $this->messageApplier->setIsTaxChanged($quote);
        } else {
            $this->messageApplier->setIsAddressChanged($quote);
        }
        $this->quoteRepository->save($quote);
    }
}
