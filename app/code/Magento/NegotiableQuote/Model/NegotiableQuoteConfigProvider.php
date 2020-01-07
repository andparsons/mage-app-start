<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class NegotiableQuoteConfigProvider
 */
class NegotiableQuoteConfigProvider implements ConfigProviderInterface
{
    /**
     * Template for key customer address
     */
    private $keyCustomerAddress = 'customer-address';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * NegotiableQuoteConfigProvider constructor
     *
     * @param Context $context
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param Session $session
     */
    public function __construct(
        Context $context,
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        CartRepositoryInterface $quoteRepository,
        AddressRepositoryInterface $addressRepository,
        Session $session
    ) {
        $this->context = $context;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'isNegotiableQuote' => (bool)$this->context->getRequest()->getParam('negotiableQuoteId'),
            'isAddressSelected' => $this->isAddressSelected(),
            'isAddressInAddressBook' => $this->isAddressInAddressBook(),
            'negotiableQuoteId' => $this->getQuoteId(),
            'backQuoteUrl' => $this->getBackQuoteUrl(),
            'selectedShippingKey' => $this->getShippingKey(),
            'quoteShippingAddress' => $this->getQuoteShippingAddress(),
            'selectedPaymentMethod' => $this->getQuotePaymentMethod(),
            'selectedShipping' => $this->getShippingMethod(),
            'isDiscountFieldLocked' => $this->isDiscountFieldLocked(),
            'isQuoteAddressLocked' => $this->isQuoteAddressLocked(),
            'isNegotiableShippingPriceSet' => $this->isNegotiableShippingPriceSet()
        ];
    }

    /**
     * Gets quote payment method
     *
     * @return null|string
     */
    private function getQuotePaymentMethod()
    {
        $result = null;
        if ($this->getQuoteId() !== null) {
            try {
                $quote = $this->quoteRepository->get($this->getQuoteId(), ['*']);
                $result = $quote->getPayment()->getMethod();
            } catch (NoSuchEntityException $e) {
                // If no such entity, skip
            }
        }
        return $result;
    }

    /**
     * Gets back to quote URL
     *
     * @return string
     */
    private function getBackQuoteUrl()
    {
        $id = $this->getQuoteId();
        return $this->context->getUrl()->getUrl('negotiable_quote/quote/view', ['quote_id' => $id]);
    }

    /**
     * Get negotiable quote ID
     *
     * @return mixed
     */
    private function getQuoteId()
    {
        $negotiableQuoteId = $this->context->getRequest()->getParam('negotiableQuoteId');
        return $negotiableQuoteId ? $negotiableQuoteId : $this->session->getQuoteId();
    }

    /**
     * Check is shipping address and shipping price selected
     *
     * @return bool
     */
    private function isAddressSelected()
    {
        $result = false;

        try {
            $quote = $this->quoteRepository->get($this->getQuoteId(), ['*']);

            if ($quote->getShippingAddress() !== null) {
                $result = (bool)$quote->getShippingAddress()->getCustomerAddressId();
            }
        } catch (NoSuchEntityException $e) {
            // If no such entity, skip
        }

        return $result;
    }

    /**
     * Return is shipping address key selected
     *
     * @return string
     */
    private function getShippingKey()
    {
        $result = '';
        if ($this->getQuoteId() !== null) {
            try {
                $quote = $this->quoteRepository->get($this->getQuoteId(), ['*']);

                if ($quote->getShippingAddress() !== null
                    && $quote->getShippingAddress()->getCustomerAddressId() !== null
                ) {
                    $result = $this->keyCustomerAddress . $quote->getShippingAddress()->getCustomerAddressId();
                }
            } catch (NoSuchEntityException $e) {
                // If no such entity, skip
            }
        }
        return $result;
    }

    /**
     * Return shipping method
     *
     * @return string|null
     */
    private function getShippingMethod()
    {
        $result = null;
        if ($this->getQuoteId() !== null) {
            try {
                $quote = $this->quoteRepository->get($this->getQuoteId(), ['*']);

                if ($quote->getShippingAddress() !== null) {
                    $result = $quote->getShippingAddress()->getShippingMethod();
                }
            } catch (NoSuchEntityException $e) {
                // If no such entity, skip
            }
        }
        return $result;
    }

    /**
     * Check if there is shipping address in address book
     *
     * @return bool
     */
    private function isAddressInAddressBook()
    {
        if ($this->getQuoteId() === null) {
            return false;
        }
        try {
            $quote = $this->quoteRepository->get($this->getQuoteId(), ['*']);
            if ($quote->getShippingAddress()
                && $quote->getShippingAddress()->getCustomerAddressId()
                && $this->addressRepository->getById($quote->getShippingAddress()->getCustomerAddressId())
            ) {
                return true;
            }
        } catch (NoSuchEntityException $e) {
            // If no such entity, skip
        }
        return false;
    }

    /**
     * Get quote shipping address
     *
     * @return array|null
     */
    private function getQuoteShippingAddress()
    {
        $shippingAddressData = null;

        if ($this->getQuoteId() === null) {
            return $shippingAddressData;
        }

        $quote = null;

        try {
            $quote = $this->quoteRepository->get($this->getQuoteId(), ['*']);
        } catch (NoSuchEntityException $e) {
            // If no such entity, skip
        }

        if ($quote !== null && $quote->getShippingAddress() !== null
            && $quote->getShippingAddress()->getCustomerAddressId() !== null
        ) {
            $shippingAddressData = $quote->getShippingAddress()->getData();
        }

        return $shippingAddressData;
    }

    /**
     * Is discount field locked
     *
     * @return bool
     */
    private function isDiscountFieldLocked()
    {
        $isLocked = true;
        $quoteId = $this->context->getRequest()->getParam('negotiableQuoteId');

        if ($quoteId) {
            try {
                $quote = $this->quoteRepository->get($quoteId, ['*']);

                if ($quote->getExtensionAttributes() !== null
                    && $quote->getExtensionAttributes()->getNegotiableQuote() !== null) {
                    $status = $quote->getExtensionAttributes()->getNegotiableQuote()->getStatus();
                    $price = $quote->getExtensionAttributes()->getNegotiableQuote()->getNegotiatedPriceValue();
                    $isLocked = ($status === NegotiableQuoteInterface::STATUS_DECLINED
                        || ($status === NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN && empty($price)))
                        ? false
                        : true;
                }
            } catch (NoSuchEntityException $e) {
                // If no such entity, skip
            }
        }

        return $isLocked;
    }

    /**
     * Check if negotiable shipping price is set.
     *
     * @return bool
     */
    private function isNegotiableShippingPriceSet()
    {
        $priceSet = false;
        $quoteId = $this->context->getRequest()->getParam('negotiableQuoteId');

        if ($quoteId) {
            try {
                $quote = $this->quoteRepository->get($quoteId, ['*']);

                if ($quote->getExtensionAttributes() !== null
                    && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
                    && $quote->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice()
                ) {
                    $priceSet = true;
                }
            } catch (NoSuchEntityException $e) {
                // If no such entity, skip
            }
        }

        return $priceSet;
    }

    /**
     * Check is quote address locked.
     *
     * @return bool
     */
    private function isQuoteAddressLocked()
    {
        $isQuoteAddressLocked = false;

        if ((bool)$this->context->getRequest()->getParam('negotiableQuoteId')) {
            $quoteId = $this->context->getRequest()->getParam('negotiableQuoteId');
            try {
                $quote = $this->quoteRepository->get($quoteId, ['*']);

                if ($quote->getExtensionAttributes() !== null
                    && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
                ) {
                    $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
                    $isQuoteAddressLocked = $this->getShippingMethod()
                        && $negotiableQuote->getStatus() != NegotiableQuoteInterface::STATUS_EXPIRED;
                }
            } catch (NoSuchEntityException $e) {
                // If no such entity, skip
            }
        }
        return $isQuoteAddressLocked;
    }
}
