<?php

namespace Magento\NegotiableQuote\Model\History;

use Magento\Quote\Api\Data\CartInterface;
use Magento\NegotiableQuote\Model\Expiration;
use Magento\NegotiableQuote\Model\CommentManagementInterface;
use Magento\NegotiableQuote\Model\Quote\TotalsFactory;

/**
 * Management for snapshot information.
 */
class SnapshotInformationManagement
{
    /**
     * @var CommentManagementInterface
     */
    private $commentManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param CommentManagementInterface $commentManagement
     * @param TotalsFactory $quoteTotalsFactory
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        CommentManagementInterface $commentManagement,
        TotalsFactory $quoteTotalsFactory,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->commentManagement = $commentManagement;
        $this->quoteTotalsFactory = $quoteTotalsFactory;
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Prepare snapshot data.
     *
     * @param CartInterface $quote
     * @param array $snapshotData
     * @return array
     */
    public function prepareSnapshotData(CartInterface $quote, array $snapshotData)
    {
        $expirationPeriod = null;

        if ($quote !== null && $quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
        ) {
            $expirationPeriod = $quote->getExtensionAttributes()->getNegotiableQuote()->getExpirationPeriod();
        }

        $quoteStatus = $quote->getExtensionAttributes()->getNegotiableQuote()->getStatus();
        if ($expirationPeriod === null &&
            $quoteStatus != \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_DECLINED &&
            $quoteStatus !=
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER) {
            $snapshotData['expiration_date'] = Expiration::DATE_QUOTE_NEVER_EXPIRES;
        } else {
            $snapshotData['expiration_date'] = $expirationPeriod;
        }
        $snapshotData = $this->setSnapshotAdditionalData($quote, $snapshotData);
        $totals = $this->quoteTotalsFactory->create(['quote' => $quote]);
        $snapshotData['subtotal'] = $totals->getSubtotal();

        return $snapshotData;
    }

    /**
     * Set additional data to snapshot.
     *
     * @param CartInterface $quote
     * @param array $snapshotData
     * @return array
     */
    private function setSnapshotAdditionalData(CartInterface $quote, array $snapshotData)
    {
        if (!empty($this->collectNegotiatedPriceData($quote))) {
            $snapshotData['price'] = $this->collectNegotiatedPriceData($quote);
        }
        if (!empty($this->collectShippingData($quote))) {
            $snapshotData['shipping'] = $this->collectShippingData($quote);
        }
        if ($this->collectAddressData($quote)) {
            $snapshotData['address'] = $this->collectAddressData($quote);
        }
        return $snapshotData;
    }

    /**
     * Collect shipping method and price.
     *
     * @param CartInterface $quote
     * @return array
     */
    private function collectShippingData(CartInterface $quote)
    {
        $shipping = [];
        if ($quote !== null && $quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null
        ) {
            if ($quote->getShippingAddress()) {
                $shippingMethod = $this->getShippingMethodTitle(
                    $quote->getShippingAddress()->getShippingMethod(),
                    $quote
                );
            }
            if (isset($shippingMethod) && !empty($shippingMethod)) {
                $shippingPrice = $quote->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice() !== null
                    ? $quote->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice()
                    : $quote->getShippingAddress()->getShippingAmount();

                $shipping = [
                    'method' => $shippingMethod,
                    'price' => (float)$shippingPrice
                ];
            }
        }
        return $shipping;
    }

    /**
     * Collect customer address data.
     *
     * @param CartInterface $quote
     * @return array
     */
    private function collectAddressData(CartInterface $quote)
    {
        $shippingAddressArray = [];
        /** @var \Magento\Quote\Api\Data\AddressInterface $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress->getPostcode()) {
            $shippingAddressArray['id'] = $shippingAddress->getCustomerAddressId();
            $shippingAddressArray['array'] = $this->getAddressArray($shippingAddress);
        }
        return $shippingAddressArray;
    }

    /**
     * Get customer address html.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return array
     */
    private function getAddressArray(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $flatAddressArray = $this->extensibleDataObjectConverter->toFlatArray(
            $address,
            [],
            \Magento\Quote\Api\Data\AddressInterface::class
        );
        $street = $address->getStreet();
        if (!empty($street) && is_array($street)) {
            // Unset flat street data
            $streetKeys = array_keys($street);
            foreach ($streetKeys as $key) {
                if (is_array($flatAddressArray)) {
                    unset($flatAddressArray[$key]);
                }
            }
            //Restore street as an array
            $flatAddressArray['street'] = $street;
        }
        return $flatAddressArray;
    }

    /**
     * Collect negotiated price data.
     *
     * @param CartInterface $quote
     * @return array
     */
    private function collectNegotiatedPriceData(CartInterface $quote)
    {
        $data = [];
        if ($quote !== null && $quote->getExtensionAttributes() !== null
            && $quote->getExtensionAttributes()->getNegotiableQuote() !== null) {
            $priceType = $quote->getExtensionAttributes()->getNegotiableQuote()->getNegotiatedPriceType();
            $priceValue = $quote->getExtensionAttributes()->getNegotiableQuote()->getNegotiatedPriceValue();
            if ($priceType && $priceValue) {
                $data = [
                    'type' => $priceType,
                    'value' => $priceValue
                ];
            }
        }
        return $data;
    }

    /**
     * Get shipping method title.
     *
     * @param string $code
     * @param CartInterface $quote
     * @return string
     */
    private function getShippingMethodTitle($code, CartInterface $quote)
    {
        if ($code) {
            foreach ($quote->getShippingAddress()->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $code) {
                    return $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                }
            }
        }
        return '';
    }

    /**
     * Collect comment data from quote.
     *
     * @param int $quoteId
     * @return array
     */
    public function collectCommentData($quoteId)
    {
        $comments = [];
        foreach ($this->commentManagement->getQuoteComments($quoteId) as $comment) {
            $comments[] = $comment->getEntityId();
        }
        return $comments;
    }

    /**
     * Collect cart data.
     *
     * @param CartInterface $quote
     * @return array
     */
    public function collectCartData(CartInterface $quote)
    {
        $cart = [];
        foreach ($quote->getItemsCollection() as $cartItem) {
            if ($cartItem->getData('parent_item_id') !== null) {
                continue;
            }

            if ($cartItem->getBuyRequest()) {
                $options = $this->negotiableQuoteHelper->retrieveCustomOptions($cartItem, false);
                if ($options) {
                    $cart[$cartItem->getItemId()]['options'] = $this->getOptionsArray($options);
                }
            }
            $cart[$cartItem->getItemId()]['product_id'] = $cartItem->getProductId();
            $cart[$cartItem->getItemId()]['sku'] = $cartItem->getSku();
            $cart[$cartItem->getItemId()]['qty'] = $cartItem->getQty();
            $cart[$cartItem->getItemId()]['name'] = $cartItem->getName();
        }
        return $cart;
    }

    /**
     * Get options array for cart item.
     *
     * @param array $options
     * @return array
     */
    private function getOptionsArray(array $options)
    {
        $optionsArray = [];
        if (isset($options['super_attribute'])) {
            $optionsArray = $this->mergeOptionsToArray($options['super_attribute'], $optionsArray);
        }
        if (isset($options['bundle_option'])) {
            $optionsArray = $this->mergeOptionsToArray($options['bundle_option'], $optionsArray);
        }
        if (isset($options['options'])) {
            $optionsArray = $this->mergeOptionsToArray($options['options'], $optionsArray);
        }

        return $optionsArray;
    }

    /**
     * Add options to array.
     *
     * @param array $options
     * @param array $optionsArray
     * @return array
     */
    private function mergeOptionsToArray(array $options, array $optionsArray)
    {
        foreach ($options as $option => $value) {
            $optionsArray[] = [
                'option' => $option,
                'value' => $value
            ];
        }
        return $optionsArray;
    }

    /**
     * Prepare system log data.
     *
     * @param array $data
     * @return array
     */
    public function prepareSystemLogData(array $data)
    {
        if (isset($data['status']['new_value']) && in_array(
            $data['status']['new_value'],
            [
                \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_DECLINED,
                \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_EXPIRED,
            ]
        )) {
            return $this->getSystemLogData($data);
        }

        return $data;
    }

    /**
     * Get system log data for declined and expired quotes.
     *
     * @param array $data
     * @return array
     */
    private function getSystemLogData(array $data)
    {
        $systemData = [];
        if (isset($data['negotiated_price'])) {
            $systemData['negotiated_price'] = $data['negotiated_price'];
            unset($data['negotiated_price']);
        }
        if (isset($data['subtotal'])) {
            $systemData['subtotal'] = $data['subtotal'];
            unset($data['subtotal']);
        }
        if (isset($data['shipping'])) {
            $systemData['shipping'] = $data['shipping'];
            unset($data['shipping']);
        }

        if (!empty($systemData)) {
            $systemData['check_system'] = true;
            $data['system_data'] = $systemData;
        }

        return $data;
    }

    /**
     * Get customer by quote.
     *
     * @param CartInterface $quote
     * @return int
     */
    public function getCustomerId(CartInterface $quote)
    {
        return (int)$this->negotiableQuoteHelper->getSalesRepresentative($quote->getId(), true);
    }
}
